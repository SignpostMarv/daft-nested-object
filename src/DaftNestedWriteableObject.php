<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DaftNestedWriteableObject
*
* @template-extends DaftNestedObject<T>
*
* @property int $intNestedLeft
* @property int $intNestedRight
* @property int $intNestedLevel
* @property int $intNestedSortOrder
* @property scalar[] $daftNestedObjectParentId
*/
interface DaftNestedWriteableObject extends DaftNestedObject
{
    public function SetIntNestedLeft(int $value) : void;

    public function SetIntNestedRight(int $value) : void;

    public function SetIntNestedLevel(int $value) : void;

    public function SetIntNestedSortOrder(int $value) : void;

    /**
    * @param scalar[] $id
    */
    public function SetDaftNestedObjectParentId(array $id) : void;
}
