<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests;

use Generator;
use RuntimeException;
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
    *
    * @param mixed ...$remainingTreeArgs
    */
    public function testRecallFullTree(
        string $treeClass,
        string $leafClass,
        ...$remainingTreeArgs
    ) : void {
        static::assertTrue(class_exists($leafClass));
        static::assertTrue(class_exists($treeClass));
        if ( ! is_a($leafClass, DaftNestedObject::class, true)) {
            static::assertTrue(is_a($leafClass, DaftNestedObject::class, true));
        }
        if ( ! is_a($treeClass, DaftNestedObjectTree::class, true)) {
            static::assertTrue(is_a($treeClass, DaftNestedObjectTree::class, true));
        }

        array_unshift($remainingTreeArgs, $leafClass);

        /**
        * @var DaftNestedObjectTree
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

        if ( ! ($a instanceof DaftNestedObject)) {
            throw new RuntimeException(
                'Object instantiation somehow not instance of ' .
                DaftNestedObject::class
            );
        }

        $b = new $leafClass([
            'id' => 2,
            'intNestedLeft' => 2,
            'intNestedRight' => 5,
            'intNestedLevel' => 0,
            'intNestedParentId' => 0,
            'intNestedSortOrder' => 0,
        ]);

        if ( ! ($b instanceof DaftNestedObject)) {
            throw new RuntimeException(
                'Object instantiation somehow not instance of ' .
                DaftNestedObject::class
            );
        }

        $c = new $leafClass([
            'id' => 3,
            'intNestedLeft' => 3,
            'intNestedRight' => 4,
            'intNestedLevel' => 1,
            'intNestedParentId' => 2,
            'intNestedSortOrder' => 0,
        ]);

        if ( ! ($c instanceof DaftNestedObject)) {
            throw new RuntimeException(
                'Object instantiation somehow not instance of ' .
                DaftNestedObject::class
            );
        }

        static::assertSame(0, $repo->CountDaftNestedObjectFullTree());
        static::assertSame(
            $repo->CountDaftNestedObjectFullTree(),
            $repo->CountDaftNestedObjectTreeWithId($repo->GetNestedObjectTreeRootId(), true, null)
        );

        $repo->RememberDaftObject($a);
        $repo->RememberDaftObject($c); // yes this is deliberately out of order
        $repo->RememberDaftObject($b);

        static::assertSame(3, $repo->CountDaftNestedObjectFullTree());
        static::assertSame(
            $repo->CountDaftNestedObjectFullTree(),
            $repo->CountDaftNestedObjectTreeWithId($repo->GetNestedObjectTreeRootId(), true, null)
        );

        $repo->RememberDaftObject($c); // yes this is deliberately out of order
        $repo->RememberDaftObject($b); // yes this is deliberately out of order
        $repo->RememberDaftObject($a); // yes this is deliberately out of order

        static::assertSame(3, $repo->CountDaftNestedObjectFullTree());
        static::assertSame(
            $repo->CountDaftNestedObjectFullTree(),
            $repo->CountDaftNestedObjectTreeWithId($repo->GetNestedObjectTreeRootId(), true, null)
        );

        /**
        * @var array<int, DaftNestedObject>
        */
        $tree = $repo->RecallDaftNestedObjectFullTree();

        static::assertSame(
            [
                $a->GetId(),
                $b->GetId(),
                $c->GetId(),
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DaftNestedObject>
        */
        $tree = $repo->RecallDaftNestedObjectFullTree(0);

        static::assertSame(
            [
                $a->GetId(),
                $b->GetId(),
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        static::assertSame(2, $repo->CountDaftNestedObjectFullTree(0));
        static::assertSame(
            $repo->CountDaftNestedObjectFullTree(),
            $repo->CountDaftNestedObjectTreeWithId($repo->GetNestedObjectTreeRootId(), true, null)
        );

        /**
        * @var array<int, DaftNestedObject>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithObject($b, true, null);

        static::assertSame(
            [
                $b->GetId(),
                $c->GetId(),
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DaftNestedObject>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithId($b->GetId(), true, null);

        static::assertSame(
            [
                $b->GetId(),
                $c->GetId(),
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        static::assertSame(2, $repo->CountDaftNestedObjectTreeWithObject($b, true, null));
        static::assertSame(2, $repo->CountDaftNestedObjectTreeWithId($b->GetId(), true, null));

        $repo->RecallDaftNestedObjectPathToObject($c, true);

        static::assertSame(
            [
                $b->GetId(),
                $c->GetId(),
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DaftNestedObject>
        */
        $tree = $repo->RecallDaftNestedObjectPathToId($c->GetId(), true);

        static::assertSame(
            [
                $b->GetId(),
                $c->GetId(),
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        static::assertSame(2, $repo->CountDaftNestedObjectPathToObject($c, true));
        static::assertSame(2, $repo->CountDaftNestedObjectPathToId($c->GetId(), true));

        /**
        * @var array<int, DaftNestedObject>
        */
        $tree = $repo->RecallDaftNestedObjectPathToObject($c, false);

        static::assertSame(
            [
                $b->GetId(),
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DaftNestedObject>
        */
        $tree = $repo->RecallDaftNestedObjectPathToId($c->GetId(), false);

        static::assertSame(
            [
                $b->GetId(),
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        static::assertSame(1, $repo->CountDaftNestedObjectPathToObject($c, false));
        static::assertSame(1, $repo->CountDaftNestedObjectPathToId($c->GetId(), false));

        /**
        * @var array<int, DaftNestedObject>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithObject($b, false, null);

        static::assertSame(
            [
                $c->GetId(),
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DaftNestedObject>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithId($b->GetId(), false, null);

        static::assertSame(
            [
                $c->GetId(),
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        static::assertSame(1, $repo->CountDaftNestedObjectTreeWithObject($b, false, null));
        static::assertSame(1, $repo->CountDaftNestedObjectTreeWithId($b->GetId(), false, null));

        /**
        * @var array<int, DaftNestedObject>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithObject($b, false, 0);

        static::assertSame(
            [
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DaftNestedObject>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithId($b->GetId(), false, 0);

        static::assertSame(
            [
            ],
            array_map(
                function (DaftNestedObject $leaf) : int {
                    return (int) $leaf->GetId();
                },
                $tree
            )
        );

        static::assertSame(0, $repo->CountDaftNestedObjectTreeWithObject($b, false, 0));
        static::assertSame(0, $repo->CountDaftNestedObjectTreeWithId($b->GetId(), false, 0));
    }

    /**
    * @dataProvider DataProviderArgs
    *
    * @param mixed ...$remainingTreeArgs
    */
    public function testThrowIfNotType(
        string $treeClass,
        string $leafClass,
        ...$remainingTreeArgs
    ) : void {
        static::assertTrue(class_exists($leafClass));
        static::assertTrue(class_exists($treeClass));
        static::assertTrue(is_a($leafClass, DaftNestedObject::class, true));
        if ( ! is_a($treeClass, DaftNestedObjectTree::class, true)) {
            static::assertTrue(is_a($treeClass, DaftNestedObjectTree::class, true));
        }

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
        * @var DaftNestedObjectTree
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
