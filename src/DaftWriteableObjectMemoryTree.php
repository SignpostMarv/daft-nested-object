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
    public function ModifyDaftNestedObjectTreeInsert(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before = false,
        bool $above = null
    ) : DaftNestedWriteableObject {
        if ($newLeaf->GetId() === $referenceLeaf->GetId()) {
            throw new InvalidArgumentException('Cannot modify leaf relative to itself!');
        }

        if (true === $above) {
            $newLeaf->AlterDaftNestedObjectParentId(
                $referenceLeaf->ObtainDaftNestedObjectParentId()
            );
            $referenceLeaf->AlterDaftNestedObjectParentId($newLeaf->GetId());

            $newLeaf = $this->StoreThenRetrieveFreshCopy($newLeaf);
            $referenceLeaf = $this->StoreThenRetrieveFreshCopy($referenceLeaf);
        } elseif (false === $above) {
            $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->GetId());
            $newLeaf = $this->StoreThenRetrieveFreshCopy($newLeaf);
        } else {
            /**
            * @var array<int, DaftNestedWriteableObject> $siblings
            */
            $siblings = array_values(array_filter(
                $this->RecallDaftNestedObjectTreeWithId(
                    $referenceLeaf->ObtainDaftNestedObjectParentId(),
                    false,
                    0
                ),
                function (DaftNestedWriteableObject $leaf) use ($newLeaf) : bool {
                    return $leaf->GetId() !== $newLeaf->GetId();
                }
            ));
            $siblingIds = [];
            $siblingSort = [];
            $j = count($siblings);

            /**
            * @var DaftNestedWriteableObject $leaf
            */
            foreach ($siblings as $leaf) {
                /**
                * @var scalar|scalar[] $siblingId
                */
                $siblingId = $leaf->GetId();
                $siblingIds[] = $siblingId;
                $siblingSort[] = $leaf->GetIntNestedSortOrder();
            }

            $pos = array_search($referenceLeaf->GetId(), $siblingIds, true);

            if (false === $pos) {
                throw new RuntimeException('Reference leaf not found in siblings tree!');
            }
            for ($i = 0; $i < $j; ++$i) {
                $siblings[$i]->SetIntNestedSortOrder(
                        $siblingSort[$i] +
                        (($before ? ($i < $pos) : ($i <= $pos)) ? -1 : 1)
                    );
                $this->StoreThenRetrieveFreshCopy($siblings[$i]);
            }
            $newLeaf->SetIntNestedSortOrder($siblingSort[$pos]);

            $newLeaf = $this->StoreThenRetrieveFreshCopy($newLeaf);
        }

        $this->RebuildTreeInefficiently();

        $newLeaf = $this->RecallDaftObject($newLeaf->GetId());

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Could not retrieve leaf from tree after rebuilding!');
        }

        return $newLeaf;
    }

    public function ModifyDaftNestedObjectTreeInsertLoose(
        $newLeaf,
        $referenceLeafId,
        bool $before = false,
        bool $above = null
    ) : DaftNestedWriteableObject {
        if ($newLeaf === $this->GetNestedObjectTreeRootId()) {
            throw new InvalidArgumentException('Cannot pass root id as new leaf');
        }

        /**
        * @var scalar|scalar[] DaftNestedWriteableObject $newLeafId
        */
        $newLeafId = $newLeaf;
        $newLeaf = (
            ($newLeaf instanceof DaftNestedWriteableObject)
                ? $newLeaf
                : $this->RecallDaftObject($newLeaf)
        );
        $referenceLeaf = $this->RecallDaftObject($referenceLeafId);

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new InvalidArgumentException(
                'Arguemnt 1 passed to ' .
                __METHOD__ .
                ' did not resolve to a leaf node!'
            );
        }

        $newLeaf = $this->StoreThenRetrieveFreshCopy($newLeaf);

        if (
            ($newLeaf instanceof DaftNestedWriteableObject) &&
            ($referenceLeaf instanceof DaftNestedWriteableObject)
        ) {
            return $this->ModifyDaftNestedObjectTreeInsert(
                $newLeaf,
                $referenceLeaf,
                $before,
                $above
            );
        } elseif ($referenceLeafId !== $this->GetNestedObjectTreeRootId()) {
            throw new InvalidArgumentException(
                'Arguemnt 2 passed to ' .
                __METHOD__ .
                ' did not resolve to a leaf node!'
            );
        }

        $tree = array_filter(
            $this->RecallDaftNestedObjectFullTree(0),
            function (DaftNestedWriteableObject $leaf) use ($newLeaf) : bool {
                return $leaf->GetId() !== $newLeaf->GetId();
            }
        );

        if (0 === count($tree)) {
            $newLeaf->SetIntNestedLeft(0);
            $newLeaf->SetIntNestedRight(1);
            $newLeaf->SetIntNestedLevel(0);
            $newLeaf->AlterDaftNestedObjectParentId($this->GetNestedObjectTreeRootId());

            return $this->StoreThenRetrieveFreshCopy($newLeaf);
        }

        /**
        * @var DaftNestedWriteableObject $referenceLeaf
        */
        $referenceLeaf = $before ? current($tree) : end($tree);

        return $this->ModifyDaftNestedObjectTreeInsert(
            $newLeaf,
            $referenceLeaf,
            $before,
            null
        );
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
                $this->ModifyDaftNestedObjectTreeInsert($alter, $replacementRoot, false, false);
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

    protected function RebuildTreeInefficiently() : void
    {
        $parentIdXref = [
            (array) $this->GetNestedObjectTreeRootId(),
        ];

        /**
        * @var array<int, array<int, DaftNestedWriteableObject>> $xRefChildren
        */
        $xRefChildren = [
            [],
        ];

        /**
        * @var array<int, scalar|scalar[]> $idXref
        */
        $idXref = [];

        $level = 0;

        $tree = $this->RecallDaftNestedObjectFullTree();

        usort($tree, function (DaftNestedWriteableObject $a, DaftNestedWriteableObject $b) : int {
            return $this->CompareObjects($a, $b);
        });

        /**
        * @var DaftNestedWriteableObject $leaf
        */
        foreach ($tree as $i => $leaf) {
            $leafParentId = $leaf->ObtainDaftNestedObjectParentId();
            $pos = array_search($leafParentId, $parentIdXref, true);

            if (false === $pos) {
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
                /**
                * @var scalar|scalar[] $leafId
                */
                $leafId = $leaf->GetId();
                $idXref[] = $leafId;
            }

            $leaf->SetIntNestedLeft(0);
            $leaf->SetIntNestedRight(0);
            $leaf->SetIntNestedLevel(0);

            $tree[$i] = $this->StoreThenRetrieveFreshCopy($leaf);
        }

        $rebuild = function (
            DaftNestedWriteableObject $leaf,
            int $level,
            int $n,
            array $parentIds,
            array $ids,
            array $children
        ) use (
            &$rebuild
        ) : int {
            /**
            * @var scalar|scalar[] $id
            */
            $id = $leaf->GetId();

            $pos = (int) array_search($id, $ids, true);

            $leaf->SetIntNestedLevel($level);
            $leaf->SetIntNestedLeft($n);

            $n += 1;

            /**
            * @var int|false $parentPos
            */
            $parentPos = array_search((array) $id, $parentIds, true);

            if (false !== $parentPos) {
                /**
                * @var DaftNestedWriteableObject $childLeaf
                */
                foreach ($children[(int) $parentPos] as $childLeaf) {
                    $n = (int) $rebuild(
                        $childLeaf,
                        $level + 1,
                        $n,
                        $parentIds,
                        $ids,
                        $children
                    );
                }
            }

            $leaf->SetIntNestedRight($n);

            $this->StoreThenRetrieveFreshCopy($leaf);

            return $n + 1;
        };

        $n = 0;

        /**
        * @var DaftNestedWriteableObject $rootLeaf
        */
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
