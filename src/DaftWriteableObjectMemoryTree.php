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

/**
* @template T as DaftNestedWriteableObject
*
* @template-extends DaftObjectMemoryTree<T>
*
* @template-implements DaftNestedWriteableObjectTree<T>
*/
abstract class DaftWriteableObjectMemoryTree extends DaftObjectMemoryTree implements DaftNestedWriteableObjectTree
{
    /**
    * @use WriteableTreeTrait<T>
    */
    use WriteableTreeTrait;

    const DEFINITELY_BELOW = false;

    const EXCLUDE_ROOT = false;

    const INSERT_AFTER = false;

    const LIMIT_ONE = 1;

    const RELATIVE_DEPTH_SAME = 0;

    const INT_ARG_INDEX_SECOND = 2;
}
