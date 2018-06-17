<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests;

use Generator;
use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedIntObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedObjectIntTree;
use SignpostMarv\DaftObject\DaftNestedObjectTree;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftObjectRepositoryTypeByClassMethodAndTypeException;
use SignpostMarv\DaftObject\Tests\TestCase as Base;

class NestedTreeTest extends Base
{
    public function DataProviderArgs() : Generator
    {
        yield from [
            [
                static::treeClass(),
                static::leafClass(),
            ],
        ];
    }

    /**
    * @dataProvider DataProviderArgs
    */
    public function testRecallFullTree(
        string $treeClass,
        string $leafClass,
        ...$remainingTreeArgs
    ) : void {
        $this->assertTrue(class_exists($leafClass));
        $this->assertTrue(class_exists($treeClass));
        $this->assertTrue(is_a($leafClass, DaftNestedObject::class, true));
        $this->assertTrue(is_a($treeClass, DaftNestedObjectTree::class, true));

        array_unshift($remainingTreeArgs, $leafClass);

        /**
        * @var DaftNestedObjectTree $repo
        */
        $repo = $treeClass::DaftObjectRepositoryByType(...$remainingTreeArgs);

        $a = new $leafClass([
            'id' => 1,
            'intNestedLeft' => 0,
            'intNestedRight' => 1,
            'intNestedLevel' => 0,
            'intNestedParentId' => 0,
            'intNestedSortOrder' => 0,
        ]);

        $b = new $leafClass([
            'id' => 2,
            'intNestedLeft' => 2,
            'intNestedRight' => 5,
            'intNestedLevel' => 0,
            'intNestedParentId' => 0,
            'intNestedSortOrder' => 0,
        ]);

        $c = new $leafClass([
            'id' => 3,
            'intNestedLeft' => 3,
            'intNestedRight' => 4,
            'intNestedLevel' => 1,
            'intNestedParentId' => 2,
            'intNestedSortOrder' => 0,
        ]);

        $this->assertSame(0, $repo->CountDaftNestedObjectFullTree());
        $this->assertSame(
            $repo->CountDaftNestedObjectFullTree(),
            $repo->CountDaftNestedObjectTreeWithId($repo->GetNestedObjectTreeRootId(), true, null)
        );

        $repo->RememberDaftObject($a);
        $repo->RememberDaftObject($c); // yes this is deliberately out of order
        $repo->RememberDaftObject($b);

        $this->assertSame(3, $repo->CountDaftNestedObjectFullTree());
        $this->assertSame(
            $repo->CountDaftNestedObjectFullTree(),
            $repo->CountDaftNestedObjectTreeWithId($repo->GetNestedObjectTreeRootId(), true, null)
        );

        $repo->RememberDaftObject($c); // yes this is deliberately out of order
        $repo->RememberDaftObject($b); // yes this is deliberately out of order
        $repo->RememberDaftObject($a); // yes this is deliberately out of order

        $this->assertSame(3, $repo->CountDaftNestedObjectFullTree());
        $this->assertSame(
            $repo->CountDaftNestedObjectFullTree(),
            $repo->CountDaftNestedObjectTreeWithId($repo->GetNestedObjectTreeRootId(), true, null)
        );

        $this->assertSame(
            [
                $a->id,
                $b->id,
                $c->id,
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectFullTree()
            )
        );

        $this->assertSame(
            [
                $a->id,
                $b->id,
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectFullTree(0)
            )
        );

        $this->assertSame(2, $repo->CountDaftNestedObjectFullTree(0));
        $this->assertSame(
            $repo->CountDaftNestedObjectFullTree(),
            $repo->CountDaftNestedObjectTreeWithId($repo->GetNestedObjectTreeRootId(), true, null)
        );

        $this->assertSame(
            [
                $b->id,
                $c->id,
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectTreeWithObject($b, true, null)
            )
        );

        $this->assertSame(
            [
                $b->id,
                $c->id,
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectTreeWithId($b->id, true, null)
            )
        );

        $this->assertSame(2, $repo->CountDaftNestedObjectTreeWithObject($b, true, null));
        $this->assertSame(2, $repo->CountDaftNestedObjectTreeWithId($b->id, true, null));

        $this->assertSame(
            [
                $b->id,
                $c->id,
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectPathToObject($c, true)
            )
        );

        $this->assertSame(
            [
                $b->id,
                $c->id,
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectPathToId($c->id, true)
            )
        );

        $this->assertSame(2, $repo->CountDaftNestedObjectPathToObject($c, true));
        $this->assertSame(2, $repo->CountDaftNestedObjectPathToId($c->id, true));

        $this->assertSame(
            [
                $b->id,
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectPathToObject($c, false)
            )
        );

        $this->assertSame(
            [
                $b->id,
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectPathToId($c->id, false)
            )
        );

        $this->assertSame(1, $repo->CountDaftNestedObjectPathToObject($c, false));
        $this->assertSame(1, $repo->CountDaftNestedObjectPathToId($c->id, false));

        $this->assertSame(
            [
                $c->id,
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectTreeWithObject($b, false, null)
            )
        );

        $this->assertSame(
            [
                $c->id,
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectTreeWithId($b->id, false, null)
            )
        );

        $this->assertSame(1, $repo->CountDaftNestedObjectTreeWithObject($b, false, null));
        $this->assertSame(1, $repo->CountDaftNestedObjectTreeWithId($b->id, false, null));

        $this->assertSame(
            [
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectTreeWithObject($b, false, 0)
            )
        );

        $this->assertSame(
            [
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return $leaf->id;
                },
                $repo->RecallDaftNestedObjectTreeWithId($b->id, false, 0)
            )
        );

        $this->assertSame(0, $repo->CountDaftNestedObjectTreeWithObject($b, false, 0));
        $this->assertSame(0, $repo->CountDaftNestedObjectTreeWithId($b->id, false, 0));
    }

    /**
    * @dataProvider DataProviderArgs
    */
    public function testThrowIfNotType(
        string $treeClass,
        string $leafClass,
        ...$remainingTreeArgs
    ) : void {
        $this->assertTrue(class_exists($leafClass));
        $this->assertTrue(class_exists($treeClass));
        $this->assertTrue(is_a($leafClass, DaftNestedObject::class, true));
        $this->assertTrue(is_a($treeClass, DaftNestedObjectTree::class, true));

        $this->expectException(DaftObjectRepositoryTypeByClassMethodAndTypeException::class);
        $this->expectExceptionMessage(sprintf(
            'Argument %u passed to %s::%s() must be an implementation of %s, %s given.',
            1,
            $treeClass,
            'DaftObjectRepositoryByType',
            (
                is_a($leafClass, DaftNestedWriteableObject::class, true)
                    ? DaftNestedWriteableObject::class
                    : DaftNestedObject::class
            ),
            AbstractArrayBackedDaftObject::class
        ));

        array_unshift($remainingTreeArgs, AbstractArrayBackedDaftObject::class);

        /**
        * @var DaftNestedObjectTree $repo
        */
        $repo = $treeClass::DaftObjectRepositoryByType(...$remainingTreeArgs);
    }

    protected static function leafClass() : string
    {
        return DaftNestedIntObject::class;
    }

    protected static function treeClass() : string
    {
        return DaftNestedObjectIntTree::class;
    }
}
