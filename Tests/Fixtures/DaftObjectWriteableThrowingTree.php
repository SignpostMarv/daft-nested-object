<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObjectTree;

/**
* @template T as DaftNestedWriteableObject
*
* @template-extends DaftObjectThrowingTree<T>
* @template-extends DaftNestedWriteableObjectTree<T>
*/
interface DaftObjectWriteableThrowingTree extends
        DaftObjectThrowingTree,
        DaftNestedWriteableObjectTree
{
    public function StoreThenRetrieveFreshLeafPublic(
        DaftNestedWriteableObject $leaf
    ) : DaftNestedWriteableObject;
}
