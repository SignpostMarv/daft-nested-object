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

class ThrowingWriteableMemoryTree extends DaftWriteableNestedObjectIntTree implements DaftObjectWriteableThrowingTree
{
    use TraitToggleRecallDaftObjectAlwaysNull;

    public function RebuildTreeInefficiently()
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
    ) {
        $this->ModifyDaftNestedObjectTreeInsertAdjacent($newLeaf, $referenceLeaf, $before);
    }
}
