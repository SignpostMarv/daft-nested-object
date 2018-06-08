<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use BadMethodCallException;
use InvalidArgumentException;
use RuntimeException;

abstract class DaftWriteableObjectMemoryTree extends DaftObjectMemoryTree implements DaftNestedWriteableObjectTree
{
    public function ModifyDaftNestedObjectTreeInsertBefore(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject {
        return $this->ModifyDaftNestedObjectTreeInsertInefficient($newLeaf, $referenceLeaf, true, null);
    }

    public function ModifyDaftNestedObjectTreeInsertAfter(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject {
        return $this->ModifyDaftNestedObjectTreeInsertInefficient($newLeaf, $referenceLeaf, false, null);
    }

    /**
    * @param mixed $newLeaf
    * @param mixed $referenceLeaf
    */
    public function ModifyDaftNestedObjectTreeInsertBeforeId(
        $newLeaf,
        $referenceLeaf
    ) : DaftNestedWriteableObject {
        return $this->ModifyDaftNestedObjectTreeInsertId($newLeaf, $referenceLeaf, true);
    }

    /**
    * @param mixed $newLeaf
    * @param mixed $referenceLeaf
    */
    public function ModifyDaftNestedObjectTreeInsertAfterId(
        $newLeaf,
        $referenceLeaf
    ) : DaftNestedWriteableObject {
        return $this->ModifyDaftNestedObjectTreeInsertId($newLeaf, $referenceLeaf, false);
    }

    public function ModifyDaftNestedObjectTreeInsertBelow(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject {
        return $this->ModifyDaftNestedObjectTreeInsertInefficient($referenceLeaf, $newLeaf, true, true);
    }

    /**
    * @param mixed $newLeafId
    * @param mixed $referenceLeafId
    */
    public function ModifyDaftNestedObjectTreeInsertBelowId(
        $newLeafId,
        $referenceLeafId
    ) : DaftNestedWriteableObject {
        if ($newLeafId === $this->GetNestedObjectTreeRootId()) {
            throw new InvalidArgumentException('Cannot use root ids as argument 1!');
        } elseif ($newLeafId instanceof DaftNestedWriteableObject) {
            $newLeafId = $this->StoreThenRetrieveFreshCopy($newLeafId)->GetId();
        }

        $newLeaf = $this->RecallDaftObject($newLeafId);

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new InvalidArgumentException('Leaf does not exist in tree!');
        } elseif ($referenceLeafId === $this->GetNestedObjectTreeRootId()) {
            $tree = array_filter(
                $this->RecallDaftNestedObjectFullTree(0),
                function (DaftNestedWriteableObject $leaf) use($newLeaf) : bool {
                    return $leaf->GetId() !== $newLeaf->GetId();
                }
            );

            if (count($tree) < 1) {
                $newLeaf->SetIntNestedLeft(0);
                $newLeaf->SetIntNestedRight(1);
                $newLeaf->SetIntNestedLevel(0);
                $newLeaf->AlterDaftNestedObjectParentId($this->GetNestedObjectTreeRootId());

                return $this->StoreThenRetrieveFreshCopy($newLeaf);
            }

            $referenceLeaf = end($tree);
        } else {
            $referenceLeaf = $this->RecallDaftObject($referenceLeafId);
        }

        if ( ! ($referenceLeaf instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Could not retrieve reference leaf from tree!');
        }

        return $this->ModifyDaftNestedObjectTreeInsertInefficient($newLeaf, $referenceLeaf, false, false);
    }

    public function ModifyDaftNestedObjectTreeInsertAbove(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject {
        return $this->ModifyDaftNestedObjectTreeInsertInefficient($newLeaf, $referenceLeaf, false, true);
    }

    /**
    * @param mixed $newLeafId
    * @param mixed $referenceLeafId
    */
    public function ModifyDaftNestedObjectTreeInsertAboveId(
        $newLeafId,
        $referenceLeafId
    ) : DaftNestedWriteableObject {
        if ($referenceLeafId === $this->GetNestedObjectTreeRootId()) {
            throw new InvalidArgumentException('Cannot insert leaf above root!');
        }

        /**
        * @var DaftNestedWriteableObject|null $newLeaf
        */
        $newLeaf = $this->RecallDaftObject($newLeafId);

        /**
        * @var DaftNestedWriteableObject|null $referenceLeaf
        */
        $referenceLeaf = $this->RecallDaftObject($referenceLeafId);

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new InvalidArgumentException(sprintf(
                'Argument 1 passed to %s was not found to be in this instance of %s',
                __METHOD__,
                static::class
            ));
        } elseif ( ! ($referenceLeaf instanceof DaftNestedWriteableObject)) {
            throw new InvalidArgumentException(sprintf(
                'Argument 2 passed to %s was not found to be in this instance of %s',
                __METHOD__,
                static::class
            ));
        }

        return $this->ModifyDaftNestedObjectTreeInsertAbove($newLeaf, $referenceLeaf);
    }

    public function ModifyDaftNestedObjectTreeRemoveWithObject(
        DaftNestedWriteableObject $root,
        ? DaftNestedWriteableObject $replacementRoot
    ) : int {
        if (
            $this->CountDaftNestedObjectTreeWithObject($root, false, null) > 0 &&
            is_null($replacementRoot)
        ) {
            throw new BadMethodCallException('Cannot leave orphan objects in a tree');
        }

        $root = $this->StoreThenRetrieveFreshCopy($root);

        $right = $root->GetIntNestedRight();
        $width = ($right - $root->GetIntNestedLeft()) + 1;

        $this->ModifyDaftNestedObjectTreeForRemoval($right, $width);

        if ( ! is_null($replacementRoot)) {
            $replacementRoot = $this->StoreThenRetrieveFreshCopy($replacementRoot);

            /**
            * @var DaftNestedWriteableObject $alter
            */
            foreach ($this->RecallDaftNestedObjectTreeWithObject($root, false, 1) as $alter) {
                $alter = $this->StoreThenRetrieveFreshCopy($alter);
                $this->ModifyDaftNestedObjectTreeInsertBelow($alter, $replacementRoot);
            }
        }

        $this->RemoveDaftObject($root);

        return $this->CountDaftNestedObjectFullTree();
    }

    /**
    * {@inheritdoc}
    */
    public function ModifyDaftNestedObjectTreeRemoveWithId($root, $replacementRoot) : int
    {
        $rootObject = $this->RecallDaftObject($root);

        if ( ! ($rootObject instanceof DaftNestedWriteableObject)) {
            return $this->CountDaftNestedObjectFullTree();
        }

        if (
            ! is_null($replacementRoot) &&
            $replacementRoot !== $this->GetNestedObjectTreeRootId()
        ) {
            $replacementRootObject = $this->RecallDaftObject($replacementRoot);

            if ( ! ($replacementRootObject instanceof DaftNestedWriteableObject)) {
                throw new InvalidArgumentException(
                    'Could not locate replacement root, cannot leave orphan objects!'
                );
            }

            return $this->ModifyDaftNestedObjectTreeRemoveWithObject(
                $rootObject,
                $replacementRootObject
            );
        }

        if (
            $this->CountDaftNestedObjectTreeWithObject($rootObject, false, null) > 0 &&
            is_null($replacementRoot)
        ) {
            throw new BadMethodCallException('Cannot leave orphan objects in a tree');
        }

        /**
        * @var DaftNestedWriteableObject $alter
        */
        foreach ($this->RecallDaftNestedObjectTreeWithObject($rootObject, false, null) as $alter) {
            $alter = $this->StoreThenRetrieveFreshCopy($alter);
            $alter->AlterDaftNestedObjectParentId($replacementRoot);
            $this->RememberDaftObject($alter);
        }

        $right = $rootObject->GetIntNestedRight();
        $width = ($right - $rootObject->GetIntNestedLeft()) + 1;

        $this->ModifyDaftNestedObjectTreeForRemoval($right, $width);

        $this->RemoveDaftObject($rootObject);

        return $this->CountDaftNestedObjectFullTree();
    }

    protected function RememberDaftObjectData(DefinesOwnIdPropertiesInterface $object) : void
    {
        static::ThrowIfNotType($object, DaftNestedWriteableObject::class, 1, __METHOD__);

        parent::RememberDaftObjectData($object);
    }

    /**
    * @param DaftObject|string $object
    */
    protected static function ThrowIfNotType(
        $object,
        string $type,
        int $argument,
        string $function
    ) : void {
        if ( ! is_a($object, DaftNestedWriteableObject::class, is_string($object))) {
            throw new DaftObjectRepositoryTypeByClassMethodAndTypeException(
                $argument,
                static::class,
                $function,
                DaftNestedWriteableObject::class,
                is_string($object) ? $object : get_class($object)
            );
        }

        parent::ThrowIfNotType($object, $type, $argument, $function);
    }

    protected function ModifyDaftNestedObjectTreeInsertAlt(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before,
        ? bool $above
    ) : DaftNestedWriteableObject {
        $newLeaf = $this->StoreThenRetrieveFreshCopy($newLeaf);
        $referenceLeaf = $this->StoreThenRetrieveFreshCopy($referenceLeaf);

        if (true === $above) {
            $refLeft = $referenceLeaf->GetIntNestedLeft();
            $refWidth = ($referenceLeaf->GetIntNestedRight() - $refLeft);
            $refLevel = $referenceLeaf->GetIntNestedLevel();

            $newRight = $newLeaf->GetIntNestedRight();
            $newLevel = $newLeaf->GetIntNestedLevel();

            /**
            * @var DaftNestedWriteableObject $alter
            */
            foreach (
                $this->RecallDaftNestedObjectTreeWithObject($referenceLeaf, true, null) as $alter
            ) {
                $alterLeft = $alter->GetIntNestedLeft();
                $alterRight = $alter->GetIntNestedRight();
                $alterWidth = $alterRight - $alterLeft;
                $alterLevel = $alter->GetIntNestedLevel();

                $alterLeftNew = ($newRight + ($alterLeft - $refLeft));

                $alter->SetIntNestedLeft($alterLeftNew);
                $alter->SetIntNestedRight($alterLeftNew + $alterWidth);
                $alter->SetIntNestedLevel($newLevel + ($alterLevel - $refLevel) + 1);

                $this->StoreThenRetrieveFreshCopy($alter);
            }

            $newLeaf->SetIntNestedRight($newRight + $refWidth + 1);
            $referenceLeaf = $this->RecallDaftObject($referenceLeaf->GetId());

            if ( ! ($referenceLeaf instanceof DaftNestedWriteableObject)) {
                throw new RuntimeException('Could not recall leaf from tree!');
            }

            $referenceLeaf->AlterDaftNestedObjectParentId($newLeaf->GetId());
            $this->StoreThenRetrieveFreshCopy($referenceLeaf);
        }

        $newLeaf = $this->StoreThenRetrieveFreshCopy($newLeaf);

        return $newLeaf;
    }

    protected function ModifyDaftNestedObjectTreeInsertInefficient(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before,
        ? bool $above
    ) : DaftNestedWriteableObject {
        $newLeafParent = $newLeaf->ObtainDaftNestedObjectParentId();
        $referenceParent = $referenceLeaf->ObtainDaftNestedObjectParentId();

        $newLeaf = $this->StoreThenRetrieveFreshCopy($newLeaf);
        $referenceLeaf = $this->StoreThenRetrieveFreshCopy($referenceLeaf);

        $newLeaf->AlterDaftNestedObjectParentId($newLeafParent);
        $referenceLeaf->AlterDaftNestedObjectParentId($referenceParent);

        if (is_null($above)) {
            $newLeaf->AlterDaftNestedObjectParentId(
                $referenceLeaf->ObtainDaftNestedObjectParentId()
            );
        } elseif (false === $above) {
            $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->GetId());
        } else {
            $referenceLeaf->AlterDaftNestedObjectParentId($newLeaf->GetId());
        }

        $parentIdXref = [
            (array) $this->GetNestedObjectTreeRootId(),
        ];
        $xRefChildren = [
            [],
        ];
        $idXref = [];

        $level = 0;

        $tree = $this->RecallDaftNestedObjectFullTree();
        $tree[] = $newLeaf;
        $tree[] = $referenceLeaf;

        $uniqueTree = [];

        foreach ($tree as $leaf) {
            if ( ! in_array($leaf->GetId(), $idXref, true)) {
                $idXref[] = $leaf->GetId();
                $uniqueTree[] = $leaf;
            }
        }

        $newLeafId = $newLeaf->GetId();

        usort(
            $uniqueTree,
            function (
                DaftNestedWriteableObject $a,
                DaftNestedWriteableObject $b
            ) use (
                $before,
                $newLeafId
            ) : int {
                if ($a->GetId() === $newLeafId) {
                    return $before ? -1 : 1;
                }

                return 0;
            }
        );

        $tree = $uniqueTree;
        unset($uniqueTree);

        foreach ($tree as $leaf) {
            $leafParentId = $leaf->ObtainDaftNestedObjectParentId();
            $pos = array_search($leafParentId, $parentIdXref, true);

            if ($pos === false) {
                $parentIdXref[] = $leafParentId;

                /**
                * @var int $pos
                */
                $pos = array_search($leafParentId, $parentIdXref, true);

                $xRefChildren[$pos] = [];
            }

            if ( ! in_array($leaf, $xRefChildren[$pos], true)) {
                $xRefChildren[$pos][] = $leaf;
            }

            if ( ! in_array($leaf->GetId(), $idXref, true)) {
                $idXref[] = $leaf->GetId();
            }
        }

        $rebuild = function (
            DaftNestedWriteableObject $leaf,
            int $level,
            int $n,
            array $parentIds,
            array $ids,
            array $children
        ) use (
            & $rebuild
        ) : int {
            $id = $leaf->GetId();

            $pos = (int) array_search($id, $ids, true);

            $leaf->SetIntNestedLevel($level);
            $leaf->SetIntNestedLeft($n);

            $parentPos = array_search($id, $parentIds, true);

            if (false !== $parentPos) {
                foreach ($children[$parentPos] as $childLeaf) {
                    $n = $rebuild(
                        $childLeaf,
                        $level + 1,
                        $n + 1,
                        $parentIds,
                        $ids,
                        $children
                    );
                }
            }

            $n += 1;

            $leaf->SetIntNestedRight($n);

            $this->StoreThenRetrieveFreshCopy($leaf);

            return $n + 1;
        };

        $n = 0;

        foreach ($xRefChildren[0] as $rootLeaf) {
            $n = $rebuild(
                $rootLeaf,
                0,
                $n,
                $parentIdXref,
                $idXref,
                $xRefChildren
            );
        }

        return $this->RecallDaftObject($newLeaf->GetId());
    }

    /**
    * @param mixed $newLeafId
    * @param mixed $referenceLeafId
    */
    protected function ModifyDaftNestedObjectTreeInsertId(
        $newLeafId,
        $referenceLeafId,
        bool $before
    ) : DaftNestedWriteableObject {
        /**
        * @var DaftNestedWriteableObject|null $newLeaf
        */
        $newLeaf =
            ($newLeafId instanceof DaftNestedWriteableObject)
                ? $newLeafId
                : $this->RecallDaftObject($newLeafId);

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Leaf could not be retrieved from argument 1!');
        }

        $referenceLeaf = null;

        if ($referenceLeafId === $this->GetNestedObjectTreeRootId()) {
            $tree = array_filter(
                $this->RecallDaftNestedObjectFullTree(0),
                function (DaftNestedWriteableObject $leaf) use ($newLeaf) : bool {
                    return $leaf->GetId() !== $newLeaf->GetId();
                }
            );

            if (count($tree) < 1) {
                $newLeaf = $this->StoreThenRetrieveFreshCopy($newLeaf);

                $newLeaf->SetIntNestedLeft(0);
                $newLeaf->SetIntNestedRight(1);
                $newLeaf->SetIntNestedLevel(0);
                $newLeaf->AlterDaftNestedObjectParentId($referenceLeafId);

                return $this->StoreThenRetrieveFreshCopy($newLeaf);;
            }

            $referenceLeaf = $before ? current($tree) : end($tree);

            return $this->ModifyDaftNestedObjectTreeInsertInefficient(
                $newLeaf,
                $referenceLeaf,
                $before,
                null
            );
        }

        $referenceLeaf = $this->RecallDaftObject($referenceLeafId);

        if ( ! ($referenceLeaf instanceof DaftNestedWriteableObject)) {
            throw new InvalidArgumentException(
                'Specified reference id does not correspond to an object in the tree'
            );
        }

        return $this->ModifyDaftNestedObjectTreeInsertInefficient($newLeaf, $referenceLeaf, $before, null);
    }

    protected function ModifyDaftNestedObjectTreeForRemoval(int $right, int $width) : void
    {
        /**
        * @var DaftNestedWriteableObject $alter
        */
        foreach ($this->RecallDaftNestedObjectFullTree() as $alter) {
            $alter = $this->StoreThenRetrieveFreshCopy($alter);

            $alterLeft = $alter->GetIntNestedLeft();
            $alterRight = $alter->GetIntNestedRight();
            $changed = false;

            if ($alterRight > $right) {
                $alter->SetIntNestedRight($alterRight - $width);
                $changed = true;
            }
            if ($alterLeft > $right) {
                $alter->SetIntNestedLeft($alterLeft - $width);
                $changed = true;
            }

            if ($changed) {
                $this->RememberDaftObject($alter);
            }
        }
    }

    protected function StoreThenRetrieveFreshCopy(
        DaftNestedWriteableObject $leaf
    ) : DaftNestedWriteableObject {
        $this->RememberDaftObject($leaf);
        $this->ForgetDaftObject($leaf);
        $this->ForgetDaftObjectById($leaf->GetId());

        $fresh = $this->RecallDaftObject($leaf->GetId());

        if ( ! ($fresh instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Was not able to obtain a fresh copy of the object!');
        }

        return $fresh;
    }
}
