<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectMemoryTree;
use SignpostMarv\DaftObject\SuitableForRepositoryType;

/**
* @template TObj as DaftNestedIntObject
*
* @template-extends DaftNestedObjectIntTree<TObj>
*
* @template-implements DaftObjectThrowingTree<TObj>
*/
class ThrowingMemoryTree extends DaftNestedObjectIntTree implements DaftObjectThrowingTree
{
    use TraitThrowingTree;

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
