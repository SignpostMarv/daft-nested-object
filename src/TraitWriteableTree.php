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

/**
* @template T as DaftNestedWriteableObject&DaftObjectCreatedByArray
*/
trait TraitWriteableTree
{
    /**
    * @use TraitWriteableTreeUtilities<T>
    */
    use TraitWriteableTreeUtilities;

    /**
    * @psalm-param T $newLeaf
    * @psalm-param T $referenceLeaf
    *
    * @psalm-return T
    */
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
    * @param (scalar|array|object|null)[] $leaf
    * @param (scalar|array|object|null)[] $referenceId
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
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $root
    * @param scalar|(scalar|array|object|null)[]|null $replacementRoot
    *
    * @psalm-param T|scalar|(scalar|array|object|null)[] $root
    *
    * @throws BadMethodCallException if $root has leaves without $replacementRoot specified
    *
    * @return int full tree count after removal
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

    /**
    * @param scalar|scalar[]|null $replacementRoot
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

            return $this->ModifyDaftNestedObjectTreeRemoveWithObject($rootObject, $replacementRoot);
        }

        /**
        * @var scalar|scalar[]
        */
        $replacementRoot = $replacementRoot;

        $this->UpdateRemoveThenRebuild($rootObject, $replacementRoot);

        return null;
    }
}
