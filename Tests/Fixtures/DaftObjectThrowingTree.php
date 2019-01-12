<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftNestedObjectTree;
use SignpostMarv\DaftObject\DaftObject;

interface DaftObjectThrowingTree extends DaftNestedObjectTree
{
    public function ToggleRecallDaftObjectAlwaysNull(bool $value);

    public function ToggleRecallDaftObjectAfterCalls(bool $value, int $after);

    /**
    * @param mixed $id
    *
    * @return DaftObject|null
    */
    public function RecallDaftObject($id);
}
