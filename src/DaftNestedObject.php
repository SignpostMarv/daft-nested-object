<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DaftObject
*
* @template-extends DaftSortableObject<T>
*
* @property-read int $intNestedLeft
* @property-read int $intNestedRight
* @property-read int $intNestedLevel
* @property-read int $intNestedSortOrder
* @property-read scalar[] $daftNestedObjectParentId
*/
interface DaftNestedObject extends SuitableForRepositoryType, DaftSortableObject
{
    public function GetIntNestedLeft() : int;

    public function GetIntNestedRight() : int;

    public function GetIntNestedLevel() : int;

    public function GetIntNestedSortOrder() : int;

    /**
    * @return scalar[]
    */
    public function GetDaftNestedObjectParentId() : array;

    /**
    * @return array<int, string>
    */
    public static function DaftNestedObjectParentIdProperties() : array;
}
