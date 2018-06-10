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
    const EXCEPTION_ARGUMENT_DID_NOT_RESOLVE_TO_A_LEAF_NODE =
        'Argument %u passed to %s() did not resolve to a leaf node!';

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

        $this->RebuildTreeInefficiently();

        $newLeaf = $this->RecallDaftObject($newLeaf->GetId());

        if ( ! ($newLeaf instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Could not retrieve leaf from tree after rebuilding!');
        }

        return $newLeaf;
    }

    public function ModifyDaftNestedObjectTreeInsertLoose(
        $leaf,
        $referenceId,
        bool $before = false,
        bool $above = null
    ) : DaftNestedWriteableObject {
        $leaf = $this->MaybeGetLeaf($leaf);

        $reference = $this->RecallDaftObject($referenceId);

        $leafIsObject = ($leaf instanceof DaftNestedWriteableObject);

        if ( ! is_null($leaf) && ($reference instanceof DaftNestedWriteableObject)) {
            return $this->ModifyDaftNestedObjectTreeInsert($leaf, $reference, $before, $above);
        } elseif ( ! $leafIsObject || ($referenceId !== $this->GetNestedObjectTreeRootId())) {
            throw new InvalidArgumentException(sprintf(
                self::EXCEPTION_ARGUMENT_DID_NOT_RESOLVE_TO_A_LEAF_NODE,
                ( ! $leafIsObject) ? 1 : 2,
                __METHOD__
            ));
        }

        /**
        * @var DaftNestedWriteableObject $leaf
        */
        $leaf = $leaf;

        return $this->ModifyDaftNestedObjectTreeInsertLooseIntoTree($leaf, $before, $above);
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

    protected function ModifyDaftNestedObjectTreeRemoveWithObjectPrepareRemovalAndRebuild(
        DaftNestedWriteableObject $root,
        DaftNestedWriteableObject $replacementRoot
    ) : void {
        /**
        * @var scalar|scalar[] $replacementRootId
        */
        $replacementRootId = $this->StoreThenRetrieveFreshCopy($replacementRoot)->GetId();

        /**
        * @var DaftNestedWriteableObject $alter
        */
        foreach ($this->RecallDaftNestedObjectTreeWithObject($root, false, 1) as $alter) {
            $alter->AlterDaftNestedObjectParentId($replacementRootId);
            $this->StoreThenRetrieveFreshCopy($alter);
        }
    }

    /**
    * {@inheritdoc}
    */
    public function ModifyDaftNestedObjectTreeRemoveWithId($root, $replacementRoot) : int
    {
        $rootObject = $this->RecallDaftObject($root);

        if ($rootObject instanceof DaftNestedWriteableObject) {
            if (
                $this->CountDaftNestedObjectTreeWithObject($rootObject, false, null) > 0 &&
                is_null($replacementRoot)
            ) {
                throw new BadMethodCallException('Cannot leave orphan objects in a tree');
            } elseif (
                ! is_null($replacementRoot) &&
                $replacementRoot !== $this->GetNestedObjectTreeRootId()
            ) {
                return $this->MaybeRemoveWithPossibleObject(
                    $rootObject,
                    $this->RecallDaftObject($replacementRoot)
                );
            }

            $this->UpdateRemoveThenRebuild($rootObject, $replacementRoot);
        }

        return $this->CountDaftNestedObjectFullTree();
    }

    /**
    * @param DaftNestedWriteableObject|mixed $leaf
    */
    protected function MaybeGetLeaf($leaf) : ? DaftNestedWriteableObject
    {
        if ($leaf === $this->GetNestedObjectTreeRootId()) {
            throw new InvalidArgumentException('Cannot pass root id as new leaf');
        } elseif ($leaf instanceof DaftNestedWriteableObject) {
            return $this->StoreThenRetrieveFreshCopy($leaf);
        }

        /**
        * @var DaftNestedWriteableObject|null $out
        */
        $out = $this->RecallDaftObject($leaf);

        return $out;
    }

    protected function ModifyDaftNestedObjectTreeInsertLooseIntoTree(
        DaftNestedWriteableObject $leaf,
        bool $before,
        ? bool $above
    ) : DaftNestedWriteableObject {
        $tree = $this->RecallDaftNestedObjectFullTree(0);
        $tree = array_filter($tree, function (DaftNestedWriteableObject $e) use ($leaf) : bool {
            return $e->GetId() !== $leaf->GetId();
        });

        if (0 === count($tree)) {
            $leaf->SetIntNestedLeft(0);
            $leaf->SetIntNestedRight(1);
            $leaf->SetIntNestedLevel(0);
            $leaf->AlterDaftNestedObjectParentId($this->GetNestedObjectTreeRootId());

            return $this->StoreThenRetrieveFreshCopy($leaf);
        }

        /**
        * @var DaftNestedWriteableObject $reference
        */
        $reference = $before ? current($tree) : end($tree);

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
    * @param mixed $replacementRoot
    */
    protected function UpdateRemoveThenRebuild(
        DaftNestedWriteableObject $rootObject,
        $replacementRoot
    ) : void {
        /**
        * @var DaftNestedWriteableObject $alter
        */
        foreach (
            $this->RecallDaftNestedObjectTreeWithObject($rootObject, false, 1) as $alter
        ) {
            $alter = $this->StoreThenRetrieveFreshCopy($alter);
            $alter->AlterDaftNestedObjectParentId($replacementRoot);
            $this->RememberDaftObject($alter);
        }

        $this->RemoveDaftObject($rootObject);

        $this->RebuildTreeInefficiently();
    }

    protected function ModifyDaftNestedObjectTreeInsertAbove(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : void {
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->ObtainDaftNestedObjectParentId());
        $referenceLeaf->AlterDaftNestedObjectParentId($newLeaf->GetId());

        $this->StoreThenRetrieveFreshCopy($newLeaf);
        $this->StoreThenRetrieveFreshCopy($referenceLeaf);
    }

    protected function ModifyDaftNestedObjectTreeInsertBelow(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : void {
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->GetId());
        $this->StoreThenRetrieveFreshCopy($newLeaf);
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
            $this->StoreThenRetrieveFreshCopy($siblings[$i]);
        }

        $newLeaf->SetIntNestedSortOrder($siblingSort[$pos]);
        $newLeaf->AlterDaftNestedObjectParentId($referenceLeaf->ObtainDaftNestedObjectParentId());

        $this->StoreThenRetrieveFreshCopy($newLeaf);
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

        $n = 0;

        /**
        * @var DaftNestedWriteableObject $rootLeaf
        */
        foreach ($xRefChildren[0] as $rootLeaf) {
            $n = $this->InefficientRebuild(
                $rootLeaf,
                0,
                $n,
                $parentIdXref,
                $idXref,
                $xRefChildren
            );
        }
    }

    protected function InefficientRebuild(
        DaftNestedWriteableObject $leaf,
        int $level,
        int $n,
        array $parentIds,
        array $ids,
        array $children
    ) : int {
        /**
        * @var scalar|scalar[] $id
        */
        $id = $leaf->GetId();

        $leaf->SetIntNestedLevel($level);
        $leaf->SetIntNestedLeft($n);

        ++$n;

        /**
        * @var int|false $parentPos
        */
        $parentPos = array_search((array) $id, $parentIds, true);

        if (false !== $parentPos) {
            /**
            * @var DaftNestedWriteableObject $childLeaf
            */
            foreach ($children[$parentPos] as $childLeaf) {
                $n = $this->InefficientRebuild(
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
