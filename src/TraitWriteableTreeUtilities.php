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
    *
    * @return DaftObject|null
    */
    abstract public function RecallDaftObject($id);

    abstract public function CountDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        int $relativeDepthLimit = null
    ) : int;

    abstract public function RemoveDaftObject(DefinesOwnIdPropertiesInterface $object);

    abstract public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int;

    abstract public function RememberDaftObject(DefinesOwnIdPropertiesInterface $object);

    abstract public function ForgetDaftObject(DefinesOwnIdPropertiesInterface $object);

    /**
    * @param mixed $id
    */
    abstract public function ForgetDaftObjectById($id);

    /**
    * @return array<int, DaftNestedObject>
    */
    abstract public function RecallDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        int $relativeDepthLimit = null
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
        int $relativeDepthLimit = null
    ) : array;

    abstract public function StoreThenRetrieveFreshLeaf(
        DaftNestedWriteableObject $leaf
    ) : DaftNestedWriteableObject;

    /**
    * @return DaftNestedWriteableObject a new instance, modified version of $newLeaf
    */
    abstract public function ModifyDaftNestedObjectTreeInsert(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before = true,
        bool $above = null
    ) : DaftNestedWriteableObject;

    /**
    * @param mixed $newLeaf can be an object or an id, MUST NOT be a root id
    * @param mixed $referenceLeaf can be an object, an id, or a root id
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
        DaftNestedWriteableObject $replacementRoot = null
    ) : int;

    protected function ModifyDaftNestedObjectTreeInsertAdjacent(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before
    ) {
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

    protected function RebuildTreeInefficiently()
    {
        /**
        * @var DaftNestedWriteableObjectTree
        */
        $tree = $this->ThrowIfNotTree();
        $rebuilder = new InefficientDaftNestedRebuild($tree);
        $rebuilder->RebuildTree();
    }

    /**
    * @return DaftNestedWriteableObject|null
    */
    private function ModifyDaftNestedObjectTreeInsertMaybeLooseIntoTree(
        DaftNestedWriteableObjectTree $tree,
        DaftNestedWriteableObject $leaf = null,
        DaftObject $reference = null,
        bool $isRoot = false,
        bool $before = false,
        bool $above = null
    ) {
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
    ) {
        /**
        * @var scalar|scalar[]
        */
        $replacementRootId = $this->StoreThenRetrieveFreshLeaf($replacementRoot)->GetId();

        $this->UpdateRoots($root, $replacementRootId);
    }

    /**
    * @param scalar|scalar[] $replacementRootId
    */
    private function UpdateRoots(DaftNestedWriteableObject $root, $replacementRootId)
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
    * @param DaftNestedWriteableObject|mixed $leaf
    *
    * @return DaftNestedWriteableObject|null
    */
    private function MaybeGetLeaf($leaf)
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
        bool $above = null
    ) : DaftNestedWriteableObject {
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
        DaftObject $replacementRootObject = null
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
    private function UpdateRemoveThenRebuild(
        DaftNestedWriteableObject $rootObject,
        $replacementRoot
    ) {
        $this->UpdateRoots($rootObject, $replacementRoot);

        $this->RemoveDaftObject($rootObject);

        $this->RebuildTreeInefficiently();
    }

    private function ModifyDaftNestedObjectTreeInsertAbove(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) {
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->ObtainDaftNestedObjectParentId());
        $referenceLeaf->AlterDaftNestedObjectParentId($newLeaf->GetId());

        $this->StoreThenRetrieveFreshLeaf($newLeaf);
        $this->StoreThenRetrieveFreshLeaf($referenceLeaf);
    }

    private function ModifyDaftNestedObjectTreeInsertBelow(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) {
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
