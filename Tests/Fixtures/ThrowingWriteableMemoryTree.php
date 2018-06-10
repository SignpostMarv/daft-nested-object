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

class ThrowingWriteableMemoryTree extends DaftWriteableNestedObjectIntTree
{
    use TraitToggleRecallDaftObjectAlwaysNull;

    public function RebuildTreeInefficiently() : void
    {
        $this->ToggleRecallDaftObjectAlwaysNull(false);

        parent::RebuildTreeInefficiently();

        $this->ToggleRecallDaftObjectAlwaysNull(true);
    }

    public function StoreThenRetrieveFreshCopyPublic(
        DaftNestedWriteableObject $leaf
    ) : DaftNestedWriteableObject {
        return $this->StoreThenRetrieveFreshCopy($leaf);
    }

    public function ModifyDaftNestedObjectTreeInsertAdjacentPublic(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before
    ) : void {
        $this->ModifyDaftNestedObjectTreeInsertAdjacent($newLeaf, $referenceLeaf, $before);
    }
}
