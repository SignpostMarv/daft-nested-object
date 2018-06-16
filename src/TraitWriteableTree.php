<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use BadMethodCallException;
use InvalidArgumentException;
use RuntimeException;

trait TraitWriteableTree
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
            $this->ModifyDaftNestedObjectTreeInsertAbove($newLeaf, $referenceLeaf);
        } elseif (false === $above) {
            $this->ModifyDaftNestedObjectTreeInsertBelow($newLeaf, $referenceLeaf);
        } else {
            $this->ModifyDaftNestedObjectTreeInsertAdjacent($newLeaf, $referenceLeaf, $before);
        }

        return $this->RebuildAfterInsert($newLeaf);
    }

    /**
    * @param mixed $leaf
    * @param mixed $referenceId
    */
    public function ModifyDaftNestedObjectTreeInsertLoose(
        $leaf,
        $referenceId,
        bool $before = false,
        bool $above = null
    ) : DaftNestedWriteableObject {
        $leaf = $this->MaybeGetLeaf($leaf);

        $reference = $this->RecallDaftObject($referenceId);

        $tree = $this->ThrowIfNotTree();

        if (
            ! is_null($leaf) &&
            (
                ($reference instanceof DaftNestedWriteableObject) ||
                ($referenceId === $tree->GetNestedObjectTreeRootId())
            )
        ) {
            if ($reference instanceof DaftNestedWriteableObject) {
                return $tree->ModifyDaftNestedObjectTreeInsert($leaf, $reference, $before, $above);
            }

            return $this->ModifyDaftNestedObjectTreeInsertLooseIntoTree($leaf, $before, $above);
        }

        throw new InvalidArgumentException(sprintf(
            'Argument %u passed to %s() did not resolve to a leaf node!',
            is_null($leaf) ? 1 : 2,
            __METHOD__
        ));
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

        $root = $this->StoreThenRetrieveFreshLeaf($root);

        if ( ! is_null($replacementRoot)) {
            $this->ModifyDaftNestedObjectTreeRemoveWithObjectPrepareRemovalAndRebuild(
                $root,
                $replacementRoot
            );
        }

        $this->RemoveDaftObject($root);

        $this->RebuildTreeInefficiently();

        return $this->CountDaftNestedObjectFullTree();
    }

    /**
    * @param mixed $root
    * @param scalar|scalar[]|null $replacementRoot
    */
    public function ModifyDaftNestedObjectTreeRemoveWithId($root, $replacementRoot) : int
    {
        $rootObject = $this->RecallDaftObject($root);

        $this->ThrowIfNotTree();

        /**
        * @var DaftNestedWriteableObjectTree&TraitWriteableTree $tree
        */
        $tree = $this;

        if ($rootObject instanceof DaftNestedWriteableObject) {
            if (
                $tree->CountDaftNestedObjectTreeWithObject($rootObject, false, null) > 0 &&
                is_null($replacementRoot)
            ) {
                throw new BadMethodCallException('Cannot leave orphan objects in a tree');
            } elseif (
                ! is_null($replacementRoot) &&
                ($tree instanceof DaftNestedWriteableObjectTree) &&
                $replacementRoot !== $tree->GetNestedObjectTreeRootId()
            ) {
                return $tree->MaybeRemoveWithPossibleObject(
                    $rootObject,
                    $tree->RecallDaftObject($replacementRoot)
                );
            }

            /**
            * @var scalar|scalar[] $replacementRoot
            */
            $replacementRoot = $replacementRoot;

            $tree->UpdateRemoveThenRebuild($rootObject, $replacementRoot);
        }

        return $this->CountDaftNestedObjectFullTree();
    }

    public function StoreThenRetrieveFreshLeaf(
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

    /**
    * @param mixed $id
    */
    abstract public function RecallDaftObject($id) : ? DaftObject;

    abstract public function CountDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : int;

    abstract public function RemoveDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    /**
    * @param mixed $id
    */
    abstract public function RemoveDaftObjectById($id) : void;

    abstract public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int;

    abstract public function RememberDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    abstract public function ForgetDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    /**
    * @param mixed $id
    */
    abstract public function ForgetDaftObjectById($id) : void;

    /**
    * @return array<int, DaftNestedObject>
    */
    abstract public function RecallDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array;

    /**
    * @return array<int, DaftNestedWriteableObject>
    */
    abstract public function RecallDaftNestedObjectFullTree(int $relativeDepthLimit = null) : array;

    /**
    * @param mixed $id
    *
    * @return array<int, DaftNestedWriteableObject>
    */
    abstract public function RecallDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array;

    protected function RebuildAfterInsert(
        DaftNestedWriteableObject $newLeaf
    ) : DaftNestedWriteableObject {
        $this->RebuildTreeInefficiently();

        $newLeaf = $this->RecallDaftObject($newLeaf->GetId());

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Could not retrieve leaf from tree after rebuilding!');
        }

        return $newLeaf;
    }

    protected function ModifyDaftNestedObjectTreeRemoveWithObjectPrepareRemovalAndRebuild(
        DaftNestedWriteableObject $root,
        DaftNestedWriteableObject $replacementRoot
    ) : void {
        /**
        * @var scalar|scalar[] $replacementRootId
        */
        $replacementRootId = $this->StoreThenRetrieveFreshLeaf($replacementRoot)->GetId();

        $this->UpdateRoots($root, $replacementRootId);
    }

    /**
    * @param scalar|scalar[] $replacementRootId
    */
    protected function UpdateRoots(DaftNestedWriteableObject $root, $replacementRootId) : void
    {
        /**
        * @var DaftNestedWriteableObject $alter
        */
        foreach ($this->RecallDaftNestedObjectTreeWithObject($root, false, 1) as $alter) {
            if ($alter instanceof DaftNestedWriteableObject) {
                $alter->AlterDaftNestedObjectParentId($replacementRootId);
                $this->RememberDaftObject($alter);
            }
        }
    }

    final protected function ThrowIfNotTree() : DaftNestedWriteableObjectTree
    {
        if ( ! ($this instanceof DaftNestedWriteableObjectTree)) {
            throw new BadMethodCallException(
                'Cannot call ' .
                __FUNCTION__ .
                ' on ' .
                static::class .
                ', class does not implement ' .
                DaftNestedWriteableObjectTree::class
            );
        }

        return $this;
    }

    /**
    * @param DaftNestedWriteableObject|mixed $leaf
    */
    protected function MaybeGetLeaf($leaf) : ? DaftNestedWriteableObject
    {
        $tree = $this->ThrowIfNotTree();

        if ($leaf === $tree->GetNestedObjectTreeRootId()) {
            throw new InvalidArgumentException('Cannot pass root id as new leaf');
        } elseif ($leaf instanceof DaftNestedWriteableObject) {
            return $tree->StoreThenRetrieveFreshLeaf($leaf);
        }

        /**
        * @var DaftNestedWriteableObject|null $out
        */
        $out = $tree->RecallDaftObject($leaf);

        return ($out instanceof DaftNestedWriteableObject) ? $out : null;
    }

    protected function ModifyDaftNestedObjectTreeInsertLooseIntoTree(
        DaftNestedWriteableObject $leaf,
        bool $before,
        ? bool $above
    ) : DaftNestedWriteableObject {
        $leaves = $this->RecallDaftNestedObjectFullTree(0);
        $leaves = array_filter($leaves, function (DaftNestedWriteableObject $e) use ($leaf) : bool {
            return $e->GetId() !== $leaf->GetId();
        });
        $tree = $this->ThrowIfNotTree();

        if (0 === count($leaves)) {
            $leaf->SetIntNestedLeft(0);
            $leaf->SetIntNestedRight(1);
            $leaf->SetIntNestedLevel(0);
            $leaf->AlterDaftNestedObjectParentId($tree->GetNestedObjectTreeRootId());

            return $tree->StoreThenRetrieveFreshLeaf($leaf);
        }

        /**
        * @var DaftNestedWriteableObject $reference
        */
        $reference = $before ? current($leaves) : end($leaves);

        return $tree->ModifyDaftNestedObjectTreeInsert($leaf, $reference, $before, $above);
    }

    protected function MaybeRemoveWithPossibleObject(
        DaftNestedWriteableObject $rootObject,
        ? DaftObject $replacementRootObject
    ) : int {
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

    /**
    * @param scalar|scalar[] $replacementRoot
    */
    protected function UpdateRemoveThenRebuild(
        DaftNestedWriteableObject $rootObject,
        $replacementRoot
    ) : void {
        $this->UpdateRoots($rootObject, $replacementRoot);

        $this->RemoveDaftObject($rootObject);

        $this->RebuildTreeInefficiently();
    }

    protected function ModifyDaftNestedObjectTreeInsertAbove(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : void {
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->ObtainDaftNestedObjectParentId());
        $referenceLeaf->AlterDaftNestedObjectParentId($newLeaf->GetId());

        $this->StoreThenRetrieveFreshLeaf($newLeaf);
        $this->StoreThenRetrieveFreshLeaf($referenceLeaf);
    }

    protected function ModifyDaftNestedObjectTreeInsertBelow(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : void {
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->GetId());
        $this->StoreThenRetrieveFreshLeaf($newLeaf);
    }

    protected function ModifyDaftNestedObjectTreeInsertAdjacent(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before
    ) : void {
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
            $this->StoreThenRetrieveFreshLeaf($siblings[$i]);
        }

        $newLeaf->SetIntNestedSortOrder($siblingSort[$pos]);
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->ObtainDaftNestedObjectParentId());

        $this->StoreThenRetrieveFreshLeaf($newLeaf);
    }

    abstract protected function RememberDaftObjectData(
        DefinesOwnIdPropertiesInterface $object
    ) : void;

    /**
    * @param DaftObject|string $object
    */
    abstract protected static function ThrowIfNotType(
        $object,
        string $type,
        int $argument,
        string $function
    ) : void;

    protected function RebuildTreeInefficiently() : void
    {
        /**
        * @var DaftNestedWriteableObjectTree $tree
        */
        $tree = $this->ThrowIfNotTree();
        $rebuilder = new InefficientDaftNestedRebuild($tree);
        $rebuilder->RebuildTree();
    }
}
