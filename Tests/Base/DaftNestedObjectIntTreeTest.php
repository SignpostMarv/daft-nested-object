<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Base;

use Generator;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedIntObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedObjectIntTree;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedWriteableIntObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftObjectRepository;
use SignpostMarv\DaftObject\SuitableForRepositoryType;
use SignpostMarv\DaftObject\Tests\DaftObjectRepository\DaftObjectRepositoryTest as BaseTest;

class DaftNestedObjectIntTreeTest extends BaseTest
{
    public static function DaftObjectRepositoryByType(string $type) : DaftObjectRepository
    {
        return DaftNestedObjectIntTree::DaftObjectRepositoryByType($type);
    }

    public static function DaftObjectRepositoryByDaftObject(
        SuitableForRepositoryType $object
    ) : DaftObjectRepository {
        return DaftNestedObjectIntTree::DaftObjectRepositoryByDaftObject($object);
    }

    public function RepositoryDataProvider() : Generator
    {
        $arrayParams = $this->RepositoryDataProviderParams();
        foreach (
            [
                DaftNestedIntObject::class,
                DaftNestedWriteableIntObject::class,
            ] as $className
        ) {
            yield array_merge(
                [
                    $className,
                    true,
                    is_a($className, DaftNestedWriteableObject::class, true),
                ],
                $arrayParams
            );
        }
    }

    protected function RepositoryDataProviderParams() : array
    {
        return [
            [
                'id' => 1,
                'intNestedLeft' => 0,
                'intNestedRight' => 0,
                'intNestedParentId' => 0,
                'intNestedLevel' => 0,
                'intNestedSortOrder' => 0,
            ],
            [
                'id' => 2,
                'intNestedLeft' => 0,
                'intNestedRight' => 0,
                'intNestedParentId' => 0,
                'intNestedLevel' => 0,
                'intNestedSortOrder' => 0,
            ],
            [
                'id' => 3,
                'intNestedLeft' => 0,
                'intNestedRight' => 0,
                'intNestedParentId' => 0,
                'intNestedLevel' => 0,
                'intNestedSortOrder' => 0,
            ],
        ];
    }
}
