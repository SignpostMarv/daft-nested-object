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
        return $this->ModifyDaftNestedObjectTreeInsert($newLeaf, $referenceLeaf, true, null);
    }

    public function ModifyDaftNestedObjectTreeInsertAfter(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject {
        return $this->ModifyDaftNestedObjectTreeInsert($newLeaf, $referenceLeaf, false, null);
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
        $this->ModifyDaftNestedObjectTreeInsert($referenceLeaf, $newLeaf, true, true);

        $newLeaf = $this->RecallDaftObject($newLeaf->GetId());

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Could not recall fresh copy of argument 1!');
        }

        return $newLeaf;
    }

    /**
    * @param mixed $newLeafId
    * @param mixed $referenceLeafId
    */
    public function ModifyDaftNestedObjectTreeInsertBelowId(
        $newLeafId,
        $referenceLeafId
    ) : DaftNestedWriteableObject {
        if (
            $newLeafId === $this->GetNestedObjectTreeRootId() &&
            $referenceLeafId === $this->GetNestedObjectTreeRootId()
        ) {
            throw new InvalidArgumentException('Cannot use both root ids!');
        }

        /**
        * @var DaftNestedWriteableObject|scalar|array $wasId
        */
        $wasId = $newLeafId;

        /**
        * @var DaftNestedWriteableObject|scalar|array $wasReferenceId
        */
        $wasReferenceId = $referenceLeafId;

        /**
        * @var scalar|scalar[]
        */
        $newLeafId =
            ($newLeafId instanceof DaftNestedWriteableObject)
                ? $newLeafId->GetId()
                : $newLeafId;
        $newLeaf = $this->RecallDaftObject($newLeafId);
        $referenceLeaf = null;

        /**
        * @var DaftNestedWriteableObject|array $newLeaf
        */
        $newLeaf =
            ($newLeaf instanceof DaftNestedWriteableObject)
                ? $newLeaf
                : $wasId;

        if ($referenceLeafId instanceof DaftNestedWriteableObject) {
            $referenceLeafId = (array) $referenceLeafId->GetId();
        }

        if (
            $newLeafId !== $this->GetNestedObjectTreeRootId() &&
            ! ($newLeaf instanceof DaftNestedWriteableObject)
        ) {
            throw new InvalidArgumentException(sprintf(
                'Argument 1 passed to %s was not found to be in this instance of %s',
                __METHOD__,
                static::class
            ));
        }

        if ($referenceLeafId === $this->GetNestedObjectTreeRootId()) {
            return $this->ModifyDaftNestedObjectTreeInsertAfterId($newLeaf, 0);
        } else {
            $referenceLeaf = $this->RecallDaftObject($referenceLeafId);

            if (
                ! ($referenceLeaf instanceof DaftNestedWriteableObject) &&
                $newLeafId === $this->GetNestedObjectTreeRootId() &&
                ($wasReferenceId instanceof DaftNestedWriteableObject)
            ) {
                $referenceLeaf = $wasReferenceId;
            }

            if ( ! ($referenceLeaf instanceof DaftNestedWriteableObject)) {
                throw new InvalidArgumentException('Could not find reference leaf!');
            }

            $referenceLeaf = $this->StoreThenRetrieveFreshCopy($referenceLeaf);

            if ($newLeafId === $this->GetNestedObjectTreeRootId()) {
                $tree = array_filter(
                    $this->RecallDaftNestedObjectFullTree(0),
                    function(DaftNestedWriteableObject $leaf) use($referenceLeaf) : bool {
                        return $leaf->GetId() !== $referenceLeaf->GetId();
                    }
                );

                if (count($tree) < 1) {
                    $referenceLeaf->SetIntNestedLeft(0);
                    $referenceLeaf->SetIntNestedRight(1);
                    $referenceLeaf->SetIntNestedLevel(0);
                    $referenceLeaf->AlterDaftNestedObjectParentId(
                        $this->GetNestedObjectTreeRootId()
                    );

                    return $this->StoreThenRetrieveFreshCopy($referenceLeaf);
                }

                /**
                * @var DaftNestedWriteableObject $treeEnd
                */
                $treeEnd = end($tree);

                $last = $treeEnd->GetIntNestedRight();

                $referenceLeaf->SetIntNestedLeft($last + 1);
                $referenceLeaf->SetIntNestedRight($last + 2);
                $referenceLeaf->SetIntNestedLevel(0);
                $referenceLeaf->AlterDaftNestedObjectParentId(
                    $this->GetNestedObjectTreeRootId()
                );

                return $this->StoreThenRetrieveFreshCopy($referenceLeaf);
            }

            if (! ($newLeaf instanceof DaftNestedWriteableObject)) {
                throw new InvalidArgumentException(sprintf(
                    'Argument 1 passed to %s was not found to be in this instance of %s',
                    __METHOD__,
                    static::class
                ));
            }

            $newLeaf = $this->StoreThenRetrieveFreshCopy($newLeaf);

            $newLeaf->AlterDaftNestedObjectParentId(
                $referenceLeaf->ObtainDaftNestedObjectParentId()
            );
        }

        if ($referenceLeaf instanceof DaftNestedWriteableObject) {
            $this->ModifyDaftNestedObjectTreeInsertBelow($newLeaf, $referenceLeaf);
        }

        $this->ForgetDaftObjectById($newLeaf->GetId());

        $out = $this->RecallDaftObject($newLeaf->GetId());

        if ( ! ($out instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Could not find writeable object in repository!');
        }

        return $out;
    }

    public function ModifyDaftNestedObjectTreeInsertAbove(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject {
        return $this->ModifyDaftNestedObjectTreeInsert($newLeaf, $referenceLeaf, true, true);
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
            return $this->ModifyDaftNestedObjectTreeInsertBelowId($referenceLeafId, $newLeafId);
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
        if ($this->CountDaftNestedObjectTreeWithObject($root, false, null) > 0 && is_null($replacementRoot)) {
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

    protected function ModifyDaftNestedObjectTreeInsert(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before,
        ? bool $above
    ) : DaftNestedWriteableObject {
        $newLeaf = $this->StoreThenRetrieveFreshCopy($newLeaf);
        $referenceLeaf = $this->StoreThenRetrieveFreshCopy($referenceLeaf);

        $width = ($newLeaf->GetIntNestedRight() - $newLeaf->GetIntNestedLeft());
        $refLeft = $referenceLeaf->GetIntNestedLeft();
        $refWidth = ($referenceLeaf->GetIntNestedRight() - $refLeft);

        $newLeft = $before
            ? ($referenceLeaf->GetIntNestedLeft() - $width)
            : ($referenceLeaf->GetIntNestedRight() + 1);

        $diff = $newLeft - $newLeaf->GetIntNestedLeft();

        /**
        * @var DaftNestedWriteableObject $alter
        */
        foreach ($this->RecallDaftNestedObjectTreeWithObject($newLeaf, false, null) as $alter) {
            $alterLeft = $alter->GetIntNestedLeft();
            $alterRight = $alter->GetIntNestedRight();
            $alter->SetIntNestedLeft($alterLeft + $diff);
            $alter->SetIntNestedRight($alterRight + $diff);

            $alter = $this->StoreThenRetrieveFreshCopy($alter);
        }

        if ( ! is_null($above)) {
            $referenceLeaf = $this->RecallDaftObject($referenceLeaf->GetId());


            if ( ! ($referenceLeaf instanceof DaftNestedWriteableObject)) {
                throw new RuntimeException(
                    'Reference leaf could not be freshly recalled from tree!'
                );
            }
        }

        if ( ! is_null($above)) {
            $newLeft =
                $above
                    ? ($referenceLeaf->GetIntNestedLeft() - 2)
                    : ($referenceLeaf->GetIntNestedRight() + 1);
            $referenceWidth = $referenceLeaf->GetIntNestedRight() - $newLeft - 2;
            $newRight = $newLeft + $referenceWidth + $width;

            $referenceParent = $referenceLeaf->ObtainDaftNestedObjectParentId();

            $referenceLeaf = $this->StoreThenRetrieveFreshCopy($referenceLeaf);

            /**
            * @var DaftNestedWriteableObject $alter
            */
            foreach (
                $this->RecallDaftNestedObjectTreeWithObject($referenceLeaf, true, null) as $alter
            ) {
                $level = $alter->GetIntNestedLevel();

                $alter->SetIntNestedLevel($level + 1);

                $this->StoreThenRetrieveFreshCopy($alter);
            }

            /**
            * @var DaftNestedWriteableObject $alter
            */
            foreach ($this->RecallDaftNestedObjectFullTree() as $alter) {
                $alterLeft = $alter->GetIntNestedLeft();
                if ($alterLeft >= $referenceLeaf->GetIntNestedLeft()) {
                    $alter->SetIntNestedLeft($alterLeft - 1);
                    $alter->SetIntNestedRight($alter->GetIntNestedRight() - 1);
                }

                $this->StoreThenRetrieveFreshCopy($alter);
            }

            $referenceLeaf = $this->RecallDaftObject($referenceLeaf->GetId());

            if ( ! ($referenceLeaf instanceof DaftNestedWriteableObject)) {
                throw new RuntimeException(
                    'Reference leaf could not be freshly recalled from tree!'
                );
            }

            $newLeaf->SetIntNestedLeft($newLeft);
            $newLeaf->SetIntNestedRight($newRight + 1);
            $newLeaf->SetIntNestedLevel($referenceLeaf->GetIntNestedLevel() - 1);

            if ($newLeaf->ObtainDaftNestedObjectParentId() === $referenceLeaf->GetId()) {
                $newLeaf->AlterDaftNestedObjectParentId(
                    $referenceLeaf->ObtainDaftNestedObjectParentId()
                );
            }
        }

        $this->StoreThenRetrieveFreshCopy($newLeaf);

        $negative = array_filter(
            $this->RecallDaftNestedObjectFullTree(),
            function (DaftNestedWriteableObject $leaf) : bool {
                return $leaf->GetIntNestedLeft() < 0;
            }
        );

        if (count($negative) > 0) {
            usort(
                $negative,
                function(DaftNestedWriteableObject $a, DaftNestedWriteableObject $b) : int {
                    return $a->GetIntNestedLeft() <=> $b->GetIntNestedLeft();
                }
            );

            /**
            * @var DaftNestedWriteableObject $maxnegative
            */
            $maxnegative = current($negative);

            $diff = abs($maxnegative->GetIntNestedLeft());

            /**
            * @var DaftNestedWriteableObject $leaf
            */
            foreach ($this->RecallDaftNestedObjectFullTree() as $leaf) {
                $leaf->SetIntNestedLeft($leaf->GetIntNestedLeft() + $diff);
                $leaf->SetIntNestedRight($leaf->GetIntNestedRight() + $diff);

                $this->StoreThenRetrieveFreshCopy($leaf);
            }
        }

        $newLeaf = $this->RecallDaftObject($newLeaf->GetId());

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException(
                'Reference leaf could not be freshly recalled from tree!'
            );
        }

        return $newLeaf;

        $newRight = $newLeft + $width;

        $refWidth = $referenceLeaf->GetIntNestedRight() - $referenceLeaf->GetIntNestedLeft();

        $refLeft =
            $before
                ? $referenceLeaf->GetIntNestedRight()
                : $referenceLeaf->GetIntNestedLeft();

        $newLeft = $before
                ? ($referenceLeaf->GetIntNestedLeft() - $width)
                : ($referenceLeaf->GetIntNestedRight() + 1);

        $newRight = (is_null($above) || false === $above) ? $width : ($width + $refWidth);

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Argument 1 could not be recalled successfully!');
        } elseif ( ! ($referenceLeaf instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Argument 2 could not be recalled successfully!');
        }

        if (is_null($above)) {
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->ObtainDaftNestedObjectParentId());
        }
        if ($newLeaf->GetId() === 4) {
            var_dump($newLeaf);exit;
        }
        $newLeaf->SetIntNestedLevel(
            $referenceLeaf->GetIntNestedLevel() +
            (is_null($above) ? 0 : ($above ? -1 : 1))
        );

        /**
        * @var DaftNestedWriteableObject $alter
        */
        foreach ($this->RecallDaftNestedObjectFullTree() as $alter) {
            $alter = $this->StoreThenRetrieveFreshCopy($alter);

            $alterWidth = $alter->GetIntNestedRight() - $alter->GetIntNestedLeft();

            $alterLeft = $refLeft + $alterWidth;
            $alterRight = $alterLeft + $alterWidth;
            $changed = false;
            $this->ForgetDaftObject($alter);

            if ($alterLeft > $newLeft) {
                $alter->SetIntNestedLeft($alterLeft);
            }
            if ($alterRight > $newLeft) {
                $alter->SetIntNestedRight($alterRight);
            }


            $this->RememberDaftObject($alter);
        }

        $newLeaf->SetIntNestedLeft($newLeft);
        $newLeaf->SetIntNestedRight($newRight);

        $this->RememberDaftObject($newLeaf);

        $out = $this->RecallDaftObject($newLeaf->GetId());

        if ( ! ($out instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Could not retrieve fresh copy of specified leaf!');
        }

        return $out;
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

        $newLeaf = $this->StoreThenRetrieveFreshCopy($newLeaf);

        $referenceLeaf = null;

        if ($referenceLeafId === $this->GetNestedObjectTreeRootId()) {
            $tree = array_filter(
                $this->RecallDaftNestedObjectFullTree(0),
                function (DaftNestedWriteableObject $leaf) use ($newLeaf) : bool {
                    return $leaf->GetId() !== $newLeaf->GetId();
                }
            );

            if (count($tree) > 0) {
                if ($before) {
                    /**
                    * @var DaftNestedWriteableObject $leaf
                    */
                    foreach ($this->RecallDaftNestedObjectFullTree() as $leaf) {
                        $leaf->SetIntNestedLeft($leaf->GetIntNestedLeft() + 2);
                        $leaf->SetIntNestedRight($leaf->GetIntNestedLeft() + 2);

                        $this->StoreThenRetrieveFreshCopy($leaf);
                    }

                    $newLeaf->SetIntNestedLeft(0);
                    $newLeaf->SetIntNestedRight(1);
                    $newLeaf->SetIntNestedLevel(0);
                    $newLeaf->AlterDaftNestedObjectParentId($this->GetNestedObjectTreeRootId());

                    return $this->StoreThenRetrieveFreshCopy($newLeaf);
                }

                /**
                * @var DaftNestedWriteableObject $treeEnd
                */
                $treeEnd = end($tree);

                $reference = $treeEnd->GetIntNestedRight();

                $newLeaf->SetIntNestedLeft($reference + 1);
                $newLeaf->SetIntNestedRight($reference + 2);
                $newLeaf->SetIntNestedLevel(0);
                $newLeaf->AlterDaftNestedObjectParentId($this->GetNestedObjectTreeRootId());

                return $this->StoreThenRetrieveFreshCopy($newLeaf);
            } else {
                $newLeaf->SetIntNestedLeft(0);
                $newLeaf->SetIntNestedRight(1);
                $newLeaf->SetIntNestedLevel(0);

                return $this->StoreThenRetrieveFreshCopy($newLeaf);
            }
        }

        $referenceLeaf = $this->RecallDaftObject($referenceLeafId);

        if ( ! ($referenceLeaf instanceof DaftNestedWriteableObject)) {
            throw new InvalidArgumentException(sprintf(
                'Argument 2 passed to %s was not found to be in this instance of %s',
                __METHOD__,
                static::class
            ));
        }

        return $this->ModifyDaftNestedObjectTreeInsert($newLeaf, $referenceLeaf, $before, null);
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
