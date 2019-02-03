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
trait TraitWriteableTreeUtilities
{
    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    abstract public function RecallDaftObject($id) : ? DefinesOwnIdPropertiesInterface;

    /**
    * @psalm-param T $root
    */
    abstract public function CountDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : int;

    /**
    * @psalm-param T $object
    */
    abstract public function RemoveDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    abstract public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int;

    /**
    * @psalm-param T $object
    */
    abstract public function RememberDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    /**
    * @psalm-param T $object
    */
    abstract public function ForgetDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    abstract public function ForgetDaftObjectById($id) : void;

    /**
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
    * @return array<int, DaftNestedWriteableObject>
    */
    abstract public function RecallDaftNestedObjectFullTree(int $relativeDepthLimit = null) : array;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @return array<int, DaftNestedWriteableObject>
    */
    abstract public function RecallDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array;

    /**
    * @psalm-param T $leaf
    *
    * @psalm-return T
    */
    abstract public function StoreThenRetrieveFreshLeaf(
        DaftNestedWriteableObject $leaf
    ) : DaftNestedWriteableObject;

    /**
    * @psalm-param T $newLeaf
    * @psalm-param T $referenceLeaf
    *
    * @return DaftNestedWriteableObject a new instance, modified version of $newLeaf
    *
    * @psalm-return T
    */
    abstract public function ModifyDaftNestedObjectTreeInsert(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before = true,
        bool $above = null
    ) : DaftNestedWriteableObject;

    /**
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $newLeaf can be an object or an id, MUST NOT be a root id
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $referenceLeaf can be an object, an id, or a root id
    *
    * @psalm-param T|scalar|(scalar|array|object|null)[] $newLeaf
    * @psalm-param T|scalar|(scalar|array|object|null)[] $referenceLeaf
    *
    * @return DaftNestedWriteableObject a new instance, modified version of $newLeaf
    */
    abstract public function ModifyDaftNestedObjectTreeInsertLoose(
        $newLeaf,
        $referenceLeaf,
        bool $before = true,
        bool $above = null
    ) : DaftNestedWriteableObject;

    /**
    * @throws \BadMethodCallException if $root has leaves without $replacementRoot specified
    *
    * @return int full tree count after removal
    */
    abstract public function ModifyDaftNestedObjectTreeRemoveWithObject(
        DaftNestedWriteableObject $root,
        ? DaftNestedWriteableObject $replacementRoot
    ) : int;

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
            * @var scalar|scalar[]
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

    /**
    * @psalm-param T|null $leaf
    * @psalm-param T|null $reference
    *
    * @psalm-return T|null
    */
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
        * @var (scalar|array|object|null)[]
        */
        $id = $newLeaf->GetId();

        $newLeaf = $this->RecallDaftObject($id);

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Could not retrieve leaf from tree after rebuilding!');
        }

        return $newLeaf;
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
        * @psalm-var array<int, T>
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
    *
    * @psalm-param T|scalar|(scalar|array|object|null)[] $leaf
    *
    * @psalm-return T|null
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
        *
        * @psalm-var T|null
        */
        return $tree->RecallDaftObject($leaf);
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
        $leaves = $this->RecallDaftNestedObjectFullTree(0);
        $leaves = array_filter($leaves, function (DaftNestedWriteableObject $e) use ($leaf) : bool {
            return $e->GetId() !== $leaf->GetId();
        });
        $tree = $this->ThrowIfNotTree();

        /**
        * @var false|DaftNestedWriteableObject
        *
        * @psalm-var false|T
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
}
