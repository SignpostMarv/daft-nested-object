<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftObjectMemoryTree;
use SignpostMarv\DaftObject\TraitDaftNestedObjectIntTree;

/**
* @template TObj as DaftNestedIntObject
*
* @template-extends DaftObjectMemoryTree<TObj>
*/
class DaftNestedObjectIntTree extends DaftObjectMemoryTree
{
    use TraitDaftNestedObjectIntTree;
}
