<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftNestedObject;
use SignpostMarv\DaftObject\DaftObjectIdValuesHashLazyInt;
use SignpostMarv\DaftObject\DefinesOwnIntegerIdInterface;
use SignpostMarv\DaftObject\SuitableForRepositoryType;
use SignpostMarv\DaftObject\TypeUtilities;

/**
* @template TObj as DaftNestedIntObject
*
* @template-extends AbstractArrayBackedDaftNestedObject<TObj>
*
* @property-read int $id
* @property-read int $intNestedParentId
* @property-read int $intNestedLeft
* @property-read int $intNestedRight
* @property-read int $intNestedLevel
* @property-read int $intNestedSortOrder
* @property-read int[] $daftNestedObjectParentId
*/
class DaftNestedIntObject extends AbstractArrayBackedDaftNestedObject implements DefinesOwnIntegerIdInterface
{
    use DaftObjectIdValuesHashLazyInt;

    const PROPERTIES = [
        'intNestedLeft',
        'intNestedRight',
        'intNestedLevel',
        'id',
        'intNestedParentId',
        'intNestedSortOrder',
        'daftNestedObjectParentId',
    ];

    const EXPORTABLE_PROPERTIES = self::PROPERTIES;

    public function GetId() : int
    {
        return TypeUtilities::ExpectRetrievedValueIsIntish(
            'id',
            $this->RetrievePropertyValueFromData('id'),
            static::class
        );
    }

    public function GetIntNestedParentId() : int
    {
        return TypeUtilities::ExpectRetrievedValueIsIntish(
            'intNestedParentId',
            $this->RetrievePropertyValueFromData('intNestedParentId'),
            static::class
        );
    }

    public static function DaftObjectIdProperties() : array
    {
        return ['id'];
    }

    public static function DaftNestedObjectParentIdProperties() : array
    {
        return ['intNestedParentId'];
    }
}
