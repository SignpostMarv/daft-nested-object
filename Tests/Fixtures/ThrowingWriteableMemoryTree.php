<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

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
}
