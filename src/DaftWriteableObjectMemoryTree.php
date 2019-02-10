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

/**
* @template T as DaftNestedWriteableObject
*
* @template-extends DaftObjectMemoryTree<T>
*
* @template-implements DaftNestedWriteableObjectTree<T>
*/
abstract class DaftWriteableObjectMemoryTree extends DaftObjectMemoryTree implements DaftNestedWriteableObjectTree
{
    const DEFINITELY_BELOW = false;

    const EXCLUDE_ROOT = false;

    const INSERT_AFTER = false;

    const LIMIT_ONE = 1;

    const RELATIVE_DEPTH_SAME = 0;

    const INT_ARG_INDEX_SECOND = 2;

    /**
    * {@inheritdoc}
    *
    * @psalm-return T
    */
    public function RecallDaftNestedWriteableObjectOrThrow($id) : DaftNestedWriteableObject
    {
        /**
        * @var DaftNestedWriteableObject|null
        *
        * @psalm-var T|null
        */
        $out = $this->RecallDaftNestedObjectOrThrow($id);

        if (is_null($out)) {
            throw new DaftObjectNotRecalledException(
                'Argument 1 passed to ' .
                DaftNestedWriteableObjectTree::class .
                '::RecallDaftNestedWriteableObjectOrThrow() did not resolve to an instance of ' .
                DaftNestedWriteableObject::class .
                ' from ' .
                static::class .
                '::RecallDaftObject()'
            );
        }

        return $out;
    }

    /**
    * @psalm-param T $newLeaf
    * @psalm-param T $referenceLeaf
    *
    * @psalm-return T
    */
    public function ModifyDaftNestedObjectTreeInsert(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before = self::INSERT_AFTER,
        bool $above = null
    ) : DaftNestedWriteableObject {
        if ($newLeaf->GetId() === $referenceLeaf->GetId()) {
            throw new InvalidArgumentException('Cannot modify leaf relative to itself!');
        }

        if ((bool) $above) {
            $this->ModifyDaftNestedObjectTreeInsertAbove($newLeaf, $referenceLeaf);
        } elseif (self::DEFINITELY_BELOW === $above) {
            $this->ModifyDaftNestedObjectTreeInsertBelow($newLeaf, $referenceLeaf);
        } else {
            $this->ModifyDaftNestedObjectTreeInsertAdjacent($newLeaf, $referenceLeaf, $before);
        }

        return $this->RebuildAfterInsert($newLeaf);
    }

    /**
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $leaf
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $referenceId
    *
    * @psalm-param T|scalar|(scalar|array|object|null)[] $leaf
    * @psalm-param T|scalar|(scalar|array|object|null)[] $referenceId
    *
    * @psalm-return T
    */
    public function ModifyDaftNestedObjectTreeInsertLoose(
        $leaf,
        $referenceId,
        bool $before = self::INSERT_AFTER,
        bool $above = null
    ) : DaftNestedWriteableObject {
        /**
        * @var DaftNestedWriteableObject
        *
        * @psalm-var T
        */
        $leaf = $this->MaybeGetLeafOrThrow($leaf);

        $reference = $this->MaybeRecallLoose($referenceId);

        return $this->ModifyDaftNestedObjectTreeInsertMaybeLooseIntoTree(
            $this,
            $leaf,
            $reference,
            $referenceId === $this->GetNestedObjectTreeRootId(),
            $before,
            $above
        );
    }

    /**
    * @psalm-param T $root
    * @psalm-param T|null $replacementRoot
    */
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

    /**
    * @psalm-param T $leaf
    *
    * @psalm-return T
    */
    public function StoreThenRetrieveFreshLeaf(
        DaftNestedWriteableObject $leaf
    ) : DaftNestedWriteableObject {
        $this->RememberDaftObject($leaf);
        $this->ForgetDaftObject($leaf);
        $this->ForgetDaftObjectById($leaf->GetId());

        return $this->RecallDaftNestedWriteableObjectOrThrow($leaf->GetId());
    }

    /**
    * @psalm-param T $object
    */
    public function RememberDaftObject(SuitableForRepositoryType $object) : void
    {
        /**
        * @var DaftNestedWriteableObject
        *
        * @psalm-var T
        */
        $object = $object;

        $left = $object->GetIntNestedLeft();
        $right = $object->GetIntNestedRight();
        $level = $object->GetIntNestedLevel();

        if (0 === $left && 0 === $right && 0 === $level) {
            $fullTreeCount = $this->CountDaftNestedObjectFullTree();

            if ($fullTreeCount > AbstractArrayBackedDaftNestedObject::COUNT_EXPECT_NON_EMPTY) {
                $tree = $this->RecallDaftNestedObjectFullTree();

                /**
                * @var DaftNestedWriteableObject
                *
                * @psalm-var T
                */
                $end = end($tree);

                $left = $end->GetIntNestedRight() + 1;
            } else {
                $left = $fullTreeCount + $fullTreeCount;
            }

            $object->SetIntNestedLeft($left);
            $object->SetIntNestedRight($left + 1);
        }

        parent::RememberDaftObject($object);
    }

    /**
    * @psalm-param T $newLeaf
    * @psalm-param T $referenceLeaf
    */
    protected function ModifyDaftNestedObjectTreeInsertAdjacent(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before
    ) : void {
        /**
        * @var array<int, DaftNestedWriteableObject>
        *
        * @psalm-var array<int, T>
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
                (($before ? ($i < $pos) : ($i <= $pos)) ? self::DECREMENT : self::INCREMENT)
            );
            $this->StoreThenRetrieveFreshLeaf($siblings[$i]);
        }

        $newLeaf->SetIntNestedSortOrder($siblingSort[$pos]);
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->ObtainDaftNestedObjectParentId());

        $this->StoreThenRetrieveFreshLeaf($newLeaf);
    }

    protected function RebuildTreeInefficiently() : void
    {
        $rebuilder = new InefficientDaftNestedRebuild($this);
        $rebuilder->RebuildTree();
    }

    private function ModifyDaftNestedObjectTreeInsertMaybeLooseIntoTree(
        DaftNestedWriteableObjectTree $tree,
        DaftNestedWriteableObject $leaf,
        ? DaftNestedWriteableObject $reference,
        bool $isRoot,
        bool $before,
        ? bool $above
    ) : DaftNestedWriteableObject {
        if ($reference instanceof DaftNestedWriteableObject) {
            return $tree->ModifyDaftNestedObjectTreeInsert($leaf, $reference, $before, $above);
        }

        return $this->ModifyDaftNestedObjectTreeInsertLooseIntoTree($leaf, $before, $above);
    }

    /**
    * @psalm-param T $newLeaf
    *
    * @psalm-return T
    */
    private function RebuildAfterInsert(
        DaftNestedWriteableObject $newLeaf
    ) : DaftNestedWriteableObject {
        $this->RebuildTreeInefficiently();

        return $this->RecallDaftNestedWriteableObjectOrThrow($newLeaf->GetId());
    }

    /**
    * @psalm-param T $root
    * @psalm-param T $replacementRoot
    */
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
    *
    * @psalm-param T $root
    */
    private function UpdateRoots(DaftNestedWriteableObject $root, $replacementRootId) : void
    {
        /**
        * @var array<int, DaftNestedWriteableObject>
        *
        * @psalm-var array<int, T>
        */
        $alterThese = $this->RecallDaftNestedObjectTreeWithObject($root, false, self::LIMIT_ONE);

        foreach ($alterThese as $alter) {
            $alter->AlterDaftNestedObjectParentId($replacementRootId);
            $this->RememberDaftObject($alter);
        }
    }

    /**
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $leaf
    *
    * @psalm-param T|scalar|(scalar|array|object|null)[] $leaf
    *
    * @psalm-return T
    */
    private function MaybeGetLeafOrThrow($leaf) : DaftNestedWriteableObject
    {
        if ($leaf === $this->GetNestedObjectTreeRootId()) {
            throw new InvalidArgumentException('Cannot pass root id as new leaf');
        } elseif ($leaf instanceof DaftNestedWriteableObject) {
            return $this->StoreThenRetrieveFreshLeaf($leaf);
        }

        /**
        * @psalm-var scalar|(scalar|array|object|null)[]
        */
        $leaf = $leaf;

        return $this->RecallDaftNestedWriteableObjectOrThrow($leaf);
    }

    /**
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $leaf
    *
    * @psalm-param T|scalar|(scalar|array|object|null)[] $leaf
    */
    private function MaybeRecallLoose($leaf) : ? DaftNestedWriteableObject
    {
        if ($leaf instanceof DaftNestedWriteableObject) {
            return $leaf;
        }

        /**
        * @var scalar|(scalar|array|object|null)[]
        */
        $leaf = $leaf;

        /**
        * @var DaftNestedWriteableObject|null
        *
        * @psalm-var T|null
        */
        $out = $this->RecallDaftObject($leaf);

        return $out;
    }

    /**
    * @psalm-param T $leaf
    *
    * @psalm-return T
    */
    private function ModifyDaftNestedObjectTreeInsertLooseIntoTree(
        DaftNestedWriteableObject $leaf,
        bool $before,
        ? bool $above
    ) : DaftNestedWriteableObject {
        /**
        * @var array<int, DaftNestedWriteableObject>
        *
        * @psalm-var array<int, T>
        */
        $leaves = array_filter(
            $this->RecallDaftNestedObjectFullTree(self::RELATIVE_DEPTH_SAME),
            /**
            * @psalm-param T $e
            */
            function (DaftNestedWriteableObject $e) use ($leaf) : bool {
                return $e->GetId() !== $leaf->GetId();
            }
        );

        if (count($leaves) < 1) {
            $leaf->SetIntNestedLeft(0);
            $leaf->SetIntNestedRight(1);
            $leaf->SetIntNestedLevel(0);
            $leaf->AlterDaftNestedObjectParentId($this->GetNestedObjectTreeRootId());

            return $this->StoreThenRetrieveFreshLeaf($leaf);
        }

        /**
        * @psalm-var T
        */
        $reference = $before ? current($leaves) : end($leaves);

        return $this->ModifyDaftNestedObjectTreeInsert($leaf, $reference, $before, $above);
    }

    /**
    * @psalm-param T $rootObject
    * @psalm-param T $replacementRootObject
    */
    private function MaybeRemoveWithPossibleObject(
        DaftNestedWriteableObject $rootObject,
        DaftNestedWriteableObject $replacementRootObject
    ) : int {
        return $this->ModifyDaftNestedObjectTreeRemoveWithObject(
            $rootObject,
            $replacementRootObject
        );
    }

    /**
    * @param scalar|(scalar|array|object|null)[] $replacementRoot
    *
    * @psalm-param T $rootObject
    */
    private function UpdateRemoveThenRebuild(
        DaftNestedWriteableObject $rootObject,
        $replacementRoot
    ) : void {
        $this->UpdateRoots($rootObject, $replacementRoot);

        $this->RemoveDaftObject($rootObject);

        $this->RebuildTreeInefficiently();
    }

    /**
    * @psalm-param T $newLeaf
    * @psalm-param T $referenceLeaf
    */
    private function ModifyDaftNestedObjectTreeInsertAbove(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : void {
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->ObtainDaftNestedObjectParentId());
        $referenceLeaf->AlterDaftNestedObjectParentId($newLeaf->GetId());

        $this->StoreThenRetrieveFreshLeaf($newLeaf);
        $this->StoreThenRetrieveFreshLeaf($referenceLeaf);
    }

    /**
    * @psalm-param T $newLeaf
    * @psalm-param T $referenceLeaf
    */
    private function ModifyDaftNestedObjectTreeInsertBelow(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : void {
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->GetId());
        $this->StoreThenRetrieveFreshLeaf($newLeaf);
    }

    /**
    * @psalm-param T $newLeaf
    * @psalm-param T $referenceLeaf
    *
    * @psalm-return array<int, T>
    */
    private function SiblingsExceptLeaf(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : array {
        /**
        * @var array<int, DaftNestedWriteableObject>
        *
        * @psalm-var array<int, T>
        */
        $siblings = $this->RecallDaftNestedObjectTreeWithId(
            $referenceLeaf->ObtainDaftNestedObjectParentId(),
            self::EXCLUDE_ROOT,
            self::RELATIVE_DEPTH_SAME
        );

        $siblings = array_values(array_filter(
            $siblings,
            /**
            * @psalm-param T $leaf
            */
            function (DaftNestedWriteableObject $leaf) use ($newLeaf) : bool {
                return $leaf->GetId() !== $newLeaf->GetId();
            }
        ));

        return $siblings;
    }

    /**
    * @param scalar|(scalar|array|object|null)[]|null $replacementRoot
    *
    * @psalm-param T $rootObject
    */
    private function ModifyDaftNestedObjectTreeRemoveWithIdUsingRootObject(
        $replacementRoot,
        DaftNestedWriteableObject $rootObject
    ) : ? int {
        if (
            $this->CountDaftNestedObjectTreeWithObject(
                $rootObject,
                false,
                null
            ) > AbstractArrayBackedDaftNestedObject::COUNT_EXPECT_NON_EMPTY &&
            is_null($replacementRoot)
        ) {
            throw new BadMethodCallException('Cannot leave orphan objects in a tree');
        } elseif (
            ! is_null($replacementRoot) &&
            $replacementRoot !== $this->GetNestedObjectTreeRootId()
        ) {
            $replacementRoot = $this->RecallDaftNestedWriteableObjectOrThrow($replacementRoot);

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
