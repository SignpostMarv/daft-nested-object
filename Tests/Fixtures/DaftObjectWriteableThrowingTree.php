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
* @template TObj as DaftNestedWriteableObject
*
* @template-extends DaftObjectThrowingTree<TObj>
* @template-extends DaftNestedWriteableObjectTree<TObj>
*/
interface DaftObjectWriteableThrowingTree extends
        DaftObjectThrowingTree,
        DaftNestedWriteableObjectTree
{
    /**
    * @psalm-param TObj $leaf
    *
    * @psalm-return TObj
    */
    public function StoreThenRetrieveFreshLeafPublic(
        DaftNestedWriteableObject $leaf
    ) : DaftNestedWriteableObject;
}
