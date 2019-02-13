<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

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

    /**
    * @psalm-return T
    */
    protected function ObtainLastLeafInTree() : DaftNestedWriteableObject
    {
        $tree = $this->RecallDaftNestedObjectFullTree();

        /**
        * @var DaftNestedWriteableObject
        *
        * @psalm-var T
        */
        $end = end($tree);

        return $end;
    }
}
