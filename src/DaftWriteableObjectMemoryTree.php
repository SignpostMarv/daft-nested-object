<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DaftNestedWriteableObject&DaftObjectCreatedByArray
*
* @template-extends DaftObjectMemoryTree<T>
*/
abstract class DaftWriteableObjectMemoryTree extends DaftObjectMemoryTree implements DaftNestedWriteableObjectTree
{
    /**
    * @use TraitRememberDaftObject<T>
    */
    use TraitRememberDaftObject;

    /**
    * @use TraitWriteableTree<T>
    */
    use TraitWriteableTree;
}
