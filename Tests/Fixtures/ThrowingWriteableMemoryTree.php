<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectMemoryTree;
use SignpostMarv\DaftObject\SuitableForRepositoryType;

/**
* @template T as DaftNestedWriteableIntObject
*
* @template-extends DaftWriteableNestedObjectIntTree<T>
*
* @template-implements DaftObjectWriteableThrowingTree<T>
*/
class ThrowingWriteableMemoryTree extends DaftWriteableNestedObjectIntTree implements DaftObjectWriteableThrowingTree
{
    /**
    * @var bool
    */
    protected $ToggleRecallDaftObjectAlwaysNull = true;

    /**
    * @var bool
    */
    protected $ToggleRecallDaftObjectAfterCalls = false;

    /**
    * @var int
    */
    protected $ToggleRecallDaftObjectAfterCallsCount = 0;

    /**
    * @var int
    */
    protected $ToggleRecallDaftObjectAfterCallsAfter = 0;

    public function RebuildTreeInefficiently() : void
    {
        $this->ToggleRecallDaftObjectAlwaysNull(false);

        parent::RebuildTreeInefficiently();

        $this->ToggleRecallDaftObjectAlwaysNull(true);
    }

    public function StoreThenRetrieveFreshLeafPublic(
        DaftNestedWriteableObject $leaf
    ) : DaftNestedWriteableObject {
        return $this->StoreThenRetrieveFreshLeaf($leaf);
    }

    public function ModifyDaftNestedObjectTreeInsertAdjacentPublic(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before
    ) : void {
        $this->ModifyDaftNestedObjectTreeInsertAdjacent($newLeaf, $referenceLeaf, $before);
    }

    public function ToggleRecallDaftObjectAlwaysNull(bool $value) : void
    {
        $this->ToggleRecallDaftObjectAlwaysNull = $value;
    }

    public function ToggleRecallDaftObjectAfterCalls(bool $value, int $after) : void
    {
        if ($value) {
            $this->ToggleRecallDaftObjectAlwaysNull(false);
        }
        $this->ToggleRecallDaftObjectAfterCalls = $value;
        $this->ToggleRecallDaftObjectAfterCallsAfter = $after;
        $this->ToggleRecallDaftObjectAfterCallsCount = 0;
    }

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @psalm-return T|null
    */
    public function RecallDaftObject($id) : ? SuitableForRepositoryType
    {
        /**
        * @var scalar|scalar[]
        */
        $id = $id;

        if ($this->ToggleRecallDaftObjectAfterCalls) {
            if ((++$this->ToggleRecallDaftObjectAfterCallsCount) > $this->ToggleRecallDaftObjectAfterCallsAfter) {
                $this->ToggleRecallDaftObjectAlwaysNull(true);
            }
        }

        if ($this->ToggleRecallDaftObjectAlwaysNull) {
            return null;
        }

        /**
        * @var SuitableForRepositoryType|null
        *
        * @psalm-var T|null
        */
        $out = parent::RecallDaftObject($id);

        return $out;
    }
}
