<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DaftNestedObjectTree;
use SignpostMarv\DaftObject\SuitableForRepositoryType;

/**
* @template T as DaftNestedObject
*
* @template-extends DaftNestedObjectTree<T>
*/
interface DaftObjectThrowingTree extends DaftNestedObjectTree
{
    public function ToggleRecallDaftObjectAlwaysNull(bool $value) : void;

    public function ToggleRecallDaftObjectAfterCalls(bool $value, int $after) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function RecallDaftObject($id) : ? SuitableForRepositoryType;
}
