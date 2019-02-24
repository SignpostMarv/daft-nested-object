<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectMemoryTree;
use SignpostMarv\DaftObject\SuitableForRepositoryType;

/**
* @template TObj as DaftNestedWriteableIntObject
*
* @template-extends DaftWriteableNestedObjectIntTree<TObj>
*
* @template-implements DaftObjectWriteableThrowingTree<TObj>
*/
class ThrowingWriteableMemoryTree extends DaftWriteableNestedObjectIntTree implements DaftObjectWriteableThrowingTree
{
    use TraitThrowingTree;

    public function RebuildTreeInefficiently() : void
    {
        $this->ToggleRecallDaftObjectAlwaysNull(false);

        parent::RebuildTreeInefficiently();

        $this->ToggleRecallDaftObjectAlwaysNull(true);
    }

    /**
    * @psalm-param TObj $leaf
    *
    * @psalm-return TObj
    */
    public function StoreThenRetrieveFreshLeafPublic(
        DaftNestedWriteableObject $leaf
    ) : DaftNestedWriteableObject {
        /**
        * @psalm-var TObj
        */
        $out = $this->StoreThenRetrieveFreshLeaf($leaf);

        return $out;
    }

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @psalm-return TObj|null
    */
    public function RecallDaftObject($id) : ? SuitableForRepositoryType
    {
        $this->MaybeToggleAlwaysReturnNull();

        if ($this->ToggleRecallDaftObjectAlwaysNull) {
            return null;
        }

        /**
        * @var SuitableForRepositoryType|null
        *
        * @psalm-var TObj|null
        */
        $out = parent::RecallDaftObject($id);

        return $out;
    }
}
