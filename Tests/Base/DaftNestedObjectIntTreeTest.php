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
use SignpostMarv\DaftObject\Tests\DaftObjectRepository\DaftObjectRepositoryTest as BaseTest;

/**
* @template T as DaftNestedWriteableObject
* @template TRepo as DaftNestedObjectIntTree
*
* @template-extends BaseTest<T, TRepo>
*/
class DaftNestedObjectIntTreeTest extends BaseTest
{
    /**
    * @psalm-return class-string<TRepo>
    */
    public static function DaftObjectRepositoryClassString() : string
    {
        /**
        * @psalm-var class-string<TRepo>
        */
        $out = DaftNestedObjectIntTree::class;

        return $out;
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
