<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\WriteableObjectTrait;
use TypeError;

/**
* @template TObj as DaftNestedWriteableIntObject
*
* @template-extends DaftNestedIntObject<TObj>
*
* @template-implements DaftNestedWriteableObject<TObj>
*
* @property int $id
* @property int $intNestedParentId
* @property int $intNestedLeft
* @property int $intNestedRight
* @property int $intNestedLevel
* @property int $intNestedSortOrder
* @property int[] $daftNestedObjectParentId
*/
class DaftNestedWriteableIntObject extends DaftNestedIntObject implements DaftNestedWriteableObject
{
    /**
    * @use WriteableObjectTrait<DaftNestedWriteableIntObject>
    */
    use WriteableObjectTrait {
        SetDaftNestedObjectParentId as _SetDaftNestedObjectParentId;
    }

    public function SetIntNestedParentId(int $value) : void
    {
        $this->NudgePropertyValue('intNestedParentId', $value);
    }

    public function SetId(int $value) : void
    {
        $this->NudgePropertyValue('id', $value);
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-param array{0:int} $id
    */
    public function SetDaftNestedObjectParentId(array $id) : void
    {
        $this->_SetDaftNestedObjectParentId($id);
    }
}
