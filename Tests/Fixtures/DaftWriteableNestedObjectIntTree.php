<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftWriteableObjectMemoryTree;
use SignpostMarv\DaftObject\TraitDaftNestedObjectIntTree;

/**
* @template TObj as DaftNestedWriteableIntObject
*
* @template-extends DaftWriteableObjectMemoryTree<TObj>
*/
class DaftWriteableNestedObjectIntTree extends DaftWriteableObjectMemoryTree
{
    use TraitDaftNestedObjectIntTree;
}
