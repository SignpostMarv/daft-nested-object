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

trait TraitWriteableTreeUtilities
{
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

    protected function ModifyDaftNestedObjectTreeInsertMaybeLooseIntoTree(
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

        $reference = $before ? current($leaves) : end($leaves);

        return $this->ModifyDaftNestedObjectTreeInsert($leaf, $reference, $before, $above);
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

    protected function SiblingsExceptLeaf(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : array {
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

        return $siblings;
    }

    protected function ModifyDaftNestedObjectTreeInsertAdjacent(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before
    ) : void {
        $siblings = $this->SiblingsExceptLeaf($newLeaf, $referenceLeaf);

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
