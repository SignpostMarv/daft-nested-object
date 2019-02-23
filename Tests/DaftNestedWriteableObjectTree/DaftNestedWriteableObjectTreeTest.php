<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\DaftNestedWriteableObjectTreeTest;

use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedWriteableIntObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftWriteableNestedObjectIntTree;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObjectTree;
use SignpostMarv\DaftObject\DaftObjectMemoryRepository;
use SignpostMarv\DaftObject\DaftObjectRepository\Tests\DaftObjectMemoryRepository\DaftObjectMemoryRepositoryTest;
use SignpostMarv\DaftObject\SuitableForRepositoryType;

/**
* @template T as DaftNestedWriteableObject
* @template R as DaftObjectMemoryRepository&DaftNestedWriteableObjectTree
*
* @template-extends DaftObjectMemoryRepositoryTest<T, R>
*/
class DaftNestedWriteableObjectTreeTest extends DaftObjectMemoryRepositoryTest
{
    /**
    * @psalm-return class-string<T>
    */
    protected static function ObtainDaftObjectType() : string
    {
        return DaftNestedWriteableIntObject::class;
    }

    /**
    * @psalm-return class-string<R>
    */
    protected static function ObtainDaftObjectRepositoryType() : string
    {
        return DaftWriteableNestedObjectIntTree::class;
    }

    /**
    * @return array<string, scalar|array|object|null>
    */
    protected static function InitialData_test_DaftObjectMemoryRepository() : array
    {
        return [
            'id' => 1,
            'intNestedParentId' => 0,
            'intNestedLeft' => 0,
            'intNestedRight' => 1,
            'intNestedLevel' => 0,
            'intNestedSortOrder' => 0,
        ];
    }

    /**
    * @return array<string, scalar|array|object|null>
    */
    protected static function ChangedData_test_DaftObjectMemoryRepository() : array
    {
        return [
            'intNestedParentId' => 2,
        ];
    }

    /**
    * @psalm-param T $object
    *
    * @return array<int, string>
    */
    protected static function ExpectedChangedProperties_test_DaftObjectMemoryRepository(
        SuitableForRepositoryType $object
    ) : array {
        $out = parent::ExpectedChangedProperties_test_DaftObjectMemoryRepository($object);

        if (in_array('intNestedParentId', $out, true)) {
            $out[] = 'daftNestedObjectParentId';
        }

        return $out;
    }
}
