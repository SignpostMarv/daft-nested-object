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
*/
trait WriteableTreeTrait
{
    /**
    * @psalm-param T $root
    */
    abstract public function CountDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : int;

    abstract public function RemoveDaftObject(SuitableForRepositoryType $object) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @psalm-return T|null
    */
    abstract public function RecallDaftObject($id) : ? SuitableForRepositoryType;

    abstract public function ForgetDaftObject(SuitableForRepositoryType $object) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    abstract public function ForgetDaftObjectById($id) : void;

    /**
    * @return array<int, DaftNestedObject>
    *
    * @psalm-return array<int, T>
    */
    abstract public function RecallDaftNestedObjectFullTree(int $relativeDepthLimit = null) : array;

    /**
    * @psalm-param T $root
    *
    * @return array<int, DaftNestedObject>
    *
    * @psalm-return array<int, T>
    */
    abstract public function RecallDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array;

    /**
    * @return scalar|(scalar|array|object|null)[]
    */
    abstract public function GetNestedObjectTreeRootId();

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @return array<int, DaftNestedObject>
    *
    * @psalm-return array<int, T>
    */
    abstract public function RecallDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array;

    abstract public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int;

    /**
    * {@inheritdoc}
    *
    * @psalm-param class-string<T> $type
    *
    * @psalm-return T
    */
    abstract public function RecallDaftObjectOrThrow(
        $id,
        string $type = DaftNestedObject::class
    ) : SuitableForRepositoryType;

    /**
    * @psalm-param T $newLeaf
    * @psalm-param T $referenceLeaf
    *
    * @psalm-return T
    */
    public function ModifyDaftNestedObjectTreeInsert(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before = DaftNestedWriteableObjectTree::INSERT_AFTER,
        bool $above = null
    ) : DaftNestedWriteableObject {
        if ($newLeaf->GetId() === $referenceLeaf->GetId()) {
            throw new InvalidArgumentException('Cannot modify leaf relative to itself!');
        }

        if ((bool) $above) {
            $this->ModifyDaftNestedObjectTreeInsertAbove($newLeaf, $referenceLeaf);
        } elseif (DaftNestedWriteableObjectTree::DEFINITELY_BELOW === $above) {
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
        bool $before = DaftNestedWriteableObjectTree::INSERT_AFTER,
        bool $above = null
    ) : DaftNestedWriteableObject {
        /**
        * @var DaftNestedWriteableObject
        *
        * @psalm-var T
        */
        $leaf = $this->MaybeGetLeafOrThrow($leaf);

        $reference = $this->MaybeRecallLoose($referenceId);

        if ( ! is_null($reference)) {
            return $this->ModifyDaftNestedObjectTreeInsert($leaf, $reference, $before, $above);
        }

        return $this->ModifyDaftNestedObjectTreeInsertLooseIntoTree($leaf, $before, $above);
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
            $this->UpdateRoots(
                $root,
                $this->StoreThenRetrieveFreshLeaf($replacementRoot)->GetId()
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

        /**
        * @psalm-var class-string<T>
        */
        $type = get_class($leaf);

        /**
        * @var DaftNestedWriteableObject
        *
        * @psalm-var T
        */
        $out = $this->RecallDaftObjectOrThrow($leaf->GetId(), $type);

        return $out;
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

        if (NestedTypeParanoia::NotYetAppendedToTree($object)) {
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
                (($before ? ($i < $pos) : ($i <= $pos)) ? DaftNestedObjectTree::DECREMENT : DaftNestedObjectTree::INCREMENT)
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

    /**
    * @psalm-param T $newLeaf
    *
    * @psalm-return T
    */
    private function RebuildAfterInsert(
        DaftNestedWriteableObject $newLeaf
    ) : DaftNestedWriteableObject {
        $this->RebuildTreeInefficiently();

        /**
        * @psalm-var class-string<T>
        */
        $type = get_class($newLeaf);

        /**
        * @var DaftNestedWriteableObject
        *
        * @psalm-var T
        */
        $out = $this->RecallDaftObjectOrThrow($newLeaf->GetId(), $type);

        return $out;
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
        $alterThese = $this->RecallDaftNestedObjectTreeWithObject($root, false, DaftNestedWriteableObjectTree::LIMIT_ONE);

        foreach ($alterThese as $alter) {
            $alter->AlterDaftNestedObjectParentId($replacementRootId);
            $this->RememberDaftObject($alter);
        }
    }

    /**
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $leaf
    *
    * @psalm-param T|scalar|(scalar|array|object|null)[] $leaf
    * @psalm-param class-string<T> $type
    *
    * @psalm-return T
    */
    private function MaybeGetLeafOrThrow(
        $leaf,
        string $type = DaftNestedWriteableObject::class
    ) : DaftNestedWriteableObject {
        if ($leaf === $this->GetNestedObjectTreeRootId()) {
            throw new InvalidArgumentException('Cannot pass root id as new leaf');
        } elseif ($leaf instanceof DaftNestedWriteableObject) {
            return $this->StoreThenRetrieveFreshLeaf($leaf);
        }

        /**
        * @psalm-var scalar|(scalar|array|object|null)[]
        */
        $leaf = $leaf;

        /**
        * @var DaftNestedWriteableObject
        *
        * @psalm-var T
        */
        $out = $this->RecallDaftObjectOrThrow($leaf, $type);

        return $out;
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
            $this->RecallDaftNestedObjectFullTree(DaftNestedWriteableObjectTree::RELATIVE_DEPTH_SAME),
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

        return $this->ModifyDaftNestedObjectTreeInsert(
            $leaf,
            NestedTypeParanoia::ObtainFirstOrLast($before, ...$leaves),
            $before,
            $above
        );
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
    * @return array<int, DaftNestedWriteableObject>
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
        $out = array_values(array_filter(
            $this->RecallDaftNestedObjectTreeWithId(
                $referenceLeaf->ObtainDaftNestedObjectParentId(),
                DaftNestedWriteableObjectTree::EXCLUDE_ROOT,
                DaftNestedWriteableObjectTree::RELATIVE_DEPTH_SAME
            ),
            /**
            * @psalm-param T $leaf
            */
            function (DaftNestedWriteableObject $leaf) use ($newLeaf) : bool {
                return $leaf->GetId() !== $newLeaf->GetId();
            }
        ));

        return $out;
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
            /**
            * @psalm-var class-string<T>
            */
            $type = get_class($rootObject);

            /**
            * @var DaftNestedWriteableObject
            *
            * @psalm-var T
            */
            $replacement = $this->RecallDaftObjectOrThrow($replacementRoot, $type);

            return $this->ModifyDaftNestedObjectTreeRemoveWithObject(
                $rootObject,
                $replacement
            );
        }

        /**
        * @var scalar|(scalar|array|object|null)[]
        */
        $replacementRoot = $replacementRoot;

        $this->UpdateRoots($rootObject, $replacementRoot);

        $this->RemoveDaftObject($rootObject);

        $this->RebuildTreeInefficiently();

        return null;
    }
}
