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
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $leaf
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $referenceId
    */
    public function ModifyDaftNestedObjectTreeInsertLoose(
        $leaf,
        $referenceId,
        bool $before = false,
        bool $above = null
    ) : DaftNestedWriteableObject {
        $leaf = $this->MaybeGetLeaf($leaf);

        $reference =
            ($referenceId instanceof DaftNestedWriteableObject)
                ? $referenceId
                : $this->RecallDaftObject($referenceId);
        $tree = $this->ThrowIfNotTree();

        $resp = $this->ModifyDaftNestedObjectTreeInsertMaybeLooseIntoTree(
            $tree,
            $leaf,
            $reference,
            $referenceId === $tree->GetNestedObjectTreeRootId(),
            $before,
            $above
        );

        if ($resp instanceof DaftNestedWriteableObject) {
            return $resp;
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
            $this->CountDaftNestedObjectTreeWithObject(
                $root,
                false,
                null
            ) > AbstractArrayBackedDaftNestedObject::COUNT_EXPECT_NON_EMPTY &&
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
    * @param scalar|(scalar|array|object|null)[] $root
    * @param scalar|(scalar|array|object|null)[]|null $replacementRoot
    */
    public function ModifyDaftNestedObjectTreeRemoveWithId($root, $replacementRoot) : int
    {
        $rootObject = $this->RecallDaftObject($root);

        $resp = null;

        if ($rootObject instanceof DaftNestedWriteableObject) {
            $resp = $this->ModifyDaftNestedObjectTreeRemoveWithIdUsingRootObject(
                $replacementRoot,
                $rootObject
            );
        }

        return is_int($resp) ? $resp : $this->CountDaftNestedObjectFullTree();
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

    protected function ModifyDaftNestedObjectTreeInsertAdjacent(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before
    ) : void {
        /**
        * @var array<int, DaftNestedWriteableObject>
        */
        $siblings = $this->SiblingsExceptLeaf($newLeaf, $referenceLeaf);

        $siblingIds = [];
        $siblingSort = [];
        $j = count($siblings);

        foreach ($siblings as $leaf) {
            /**
            * @var scalar|(scalar|array|object|null)[]
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

    protected function RebuildTreeInefficiently() : void
    {
        /**
        * @var DaftNestedWriteableObjectTree
        */
        $tree = $this->ThrowIfNotTree();
        $rebuilder = new InefficientDaftNestedRebuild($tree);
        $rebuilder->RebuildTree();
    }

    private function ModifyDaftNestedObjectTreeInsertMaybeLooseIntoTree(
        DaftNestedWriteableObjectTree $tree,
        ? DaftNestedWriteableObject $leaf,
        ? DaftObject $reference,
        bool $isRoot,
        bool $before,
        ? bool $above
    ) : ? DaftNestedWriteableObject {
        if ( ! is_null($leaf) && (($reference instanceof DaftNestedWriteableObject) || $isRoot)) {
            if ($reference instanceof DaftNestedWriteableObject) {
                return $tree->ModifyDaftNestedObjectTreeInsert($leaf, $reference, $before, $above);
            }

            return $this->ModifyDaftNestedObjectTreeInsertLooseIntoTree($leaf, $before, $above);
        }

        return null;
    }

    private function RebuildAfterInsert(
        DaftNestedWriteableObject $newLeaf
    ) : DaftNestedWriteableObject {
        $this->RebuildTreeInefficiently();

        $newLeaf = $this->RecallDaftObject($newLeaf->GetId());

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Could not retrieve leaf from tree after rebuilding!');
        }

        return $newLeaf;
    }

    private function ModifyDaftNestedObjectTreeRemoveWithObjectPrepareRemovalAndRebuild(
        DaftNestedWriteableObject $root,
        DaftNestedWriteableObject $replacementRoot
    ) : void {
        /**
        * @var scalar|(scalar|array|object|null)[]
        */
        $replacementRootId = $this->StoreThenRetrieveFreshLeaf($replacementRoot)->GetId();

        $this->UpdateRoots($root, $replacementRootId);
    }

    /**
    * @param scalar|(scalar|array|object|null)[] $replacementRootId
    */
    private function UpdateRoots(DaftNestedWriteableObject $root, $replacementRootId) : void
    {
        /**
        * @var array<int, DaftNestedObject>
        */
        $alterThese = $this->RecallDaftNestedObjectTreeWithObject($root, false, 1);

        foreach ($alterThese as $alter) {
            if ($alter instanceof DaftNestedWriteableObject) {
                $alter->AlterDaftNestedObjectParentId($replacementRootId);
                $this->RememberDaftObject($alter);
            }
        }
    }

    private function ThrowIfNotTree() : DaftNestedWriteableObjectTree
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
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $leaf
    */
    private function MaybeGetLeaf($leaf) : ? DaftNestedWriteableObject
    {
        $tree = $this->ThrowIfNotTree();

        if ($leaf === $tree->GetNestedObjectTreeRootId()) {
            throw new InvalidArgumentException('Cannot pass root id as new leaf');
        } elseif ($leaf instanceof DaftNestedWriteableObject) {
            return $tree->StoreThenRetrieveFreshLeaf($leaf);
        }

        /**
        * @var DaftNestedWriteableObject|null
        */
        $out = $tree->RecallDaftObject($leaf);

        return ($out instanceof DaftNestedWriteableObject) ? $out : null;
    }

    private function ModifyDaftNestedObjectTreeInsertLooseIntoTree(
        DaftNestedWriteableObject $leaf,
        bool $before,
        ? bool $above
    ) : DaftNestedWriteableObject {
        /**
        * @var array<int, DaftNestedWriteableObject>
        */
        $leaves = $this->RecallDaftNestedObjectFullTree(0);
        $leaves = array_filter($leaves, function (DaftNestedWriteableObject $e) use ($leaf) : bool {
            return $e->GetId() !== $leaf->GetId();
        });
        $tree = $this->ThrowIfNotTree();

        /**
        * @var false|DaftNestedWriteableObject
        */
        $reference = $before ? current($leaves) : end($leaves);

        if ( ! ($reference instanceof DaftNestedWriteableObject)) {
            $leaf->SetIntNestedLeft(0);
            $leaf->SetIntNestedRight(1);
            $leaf->SetIntNestedLevel(0);
            $leaf->AlterDaftNestedObjectParentId($tree->GetNestedObjectTreeRootId());

            return $tree->StoreThenRetrieveFreshLeaf($leaf);
        }

        return $this->ModifyDaftNestedObjectTreeInsert($leaf, $reference, $before, $above);
    }

    private function MaybeRemoveWithPossibleObject(
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
    * @param scalar|(scalar|array|object|null)[] $replacementRoot
    */
    private function UpdateRemoveThenRebuild(
        DaftNestedWriteableObject $rootObject,
        $replacementRoot
    ) : void {
        $this->UpdateRoots($rootObject, $replacementRoot);

        $this->RemoveDaftObject($rootObject);

        $this->RebuildTreeInefficiently();
    }

    private function ModifyDaftNestedObjectTreeInsertAbove(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : void {
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->ObtainDaftNestedObjectParentId());
        $referenceLeaf->AlterDaftNestedObjectParentId($newLeaf->GetId());

        $this->StoreThenRetrieveFreshLeaf($newLeaf);
        $this->StoreThenRetrieveFreshLeaf($referenceLeaf);
    }

    private function ModifyDaftNestedObjectTreeInsertBelow(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : void {
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->GetId());
        $this->StoreThenRetrieveFreshLeaf($newLeaf);
    }

    private function SiblingsExceptLeaf(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : array {
        /**
        * @var array<int, DaftNestedWriteableObject>
        */
        $siblings = $this->RecallDaftNestedObjectTreeWithId(
            $referenceLeaf->ObtainDaftNestedObjectParentId(),
            false,
            0
        );

        $siblings = array_values(array_filter(
            $siblings,
            function (DaftNestedWriteableObject $leaf) use ($newLeaf) : bool {
                return $leaf->GetId() !== $newLeaf->GetId();
            }
        ));

        return $siblings;
    }

    /**
    * @param scalar|(scalar|array|object|null)[]|null $replacementRoot
    */
    private function ModifyDaftNestedObjectTreeRemoveWithIdUsingRootObject(
        $replacementRoot,
        DaftNestedWriteableObject $rootObject
    ) : ? int {
        $tree = $this->ThrowIfNotTree();

        if (
            $tree->CountDaftNestedObjectTreeWithObject(
                $rootObject,
                false,
                null
            ) > AbstractArrayBackedDaftNestedObject::COUNT_EXPECT_NON_EMPTY &&
            is_null($replacementRoot)
        ) {
            throw new BadMethodCallException('Cannot leave orphan objects in a tree');
        } elseif (
            ! is_null($replacementRoot) &&
            $replacementRoot !== $tree->GetNestedObjectTreeRootId()
        ) {
            $replacementRoot = $tree->RecallDaftObject($replacementRoot);

            return $this->MaybeRemoveWithPossibleObject($rootObject, $replacementRoot);
        }

        /**
        * @var scalar|(scalar|array|object|null)[]
        */
        $replacementRoot = $replacementRoot;

        $this->UpdateRemoveThenRebuild($rootObject, $replacementRoot);

        return null;
    }
}
