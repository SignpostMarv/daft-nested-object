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

class DaftNestedIntObject extends AbstractArrayBackedDaftNestedObject implements
    DefinesOwnIntegerIdInterface,
    SuitableForRepositoryType
{
    use DaftObjectIdValuesHashLazyInt;

    const PROPERTIES = [
        'intNestedLeft',
        'intNestedRight',
        'intNestedLevel',
        'id',
        'intNestedParentId',
        'intNestedSortOrder',
    ];

    const EXPORTABLE_PROPERTIES = self::PROPERTIES;

    public function GetId() : int
    {
        return (int) ($this->RetrievePropertyValueFromData('id') ?? null);
    }

    public function GetIntNestedParentId() : int
    {
        return (int) ($this->RetrievePropertyValueFromData('intNestedParentId') ?? null);
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
