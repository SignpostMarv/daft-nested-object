<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests;

use Generator;
use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedIntObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedObjectIntTree;
use SignpostMarv\DaftObject\DaftNestedObjectTree;
use SignpostMarv\DaftObject\DefinesOwnIntegerIdInterface;
use SignpostMarv\DaftObject\Tests\TestCase as Base;

/**
* @template T as DaftNestedObject
* @template TRepo as DaftNestedObjectTree
*/
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
    *
    * @psalm-param class-string<TRepo> $treeClass
    * @psalm-param class-string<T> $leafClass
    */
    public function testRecallFullTree(
        string $treeClass,
        string $leafClass,
        ...$remainingTreeArgs
    ) : void {
        /**
        * @psalm-var TRepo
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass, ...$remainingTreeArgs);

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
        * @var array<int, DefinesOwnIntegerIdInterface>
        */
        $tree = $repo->RecallDaftNestedObjectFullTree();

        static::assertSame(
            [
                $a->GetId(),
                $b->GetId(),
                $c->GetId(),
            ],
            array_map(
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DefinesOwnIntegerIdInterface>
        */
        $tree = $repo->RecallDaftNestedObjectFullTree(0);

        static::assertSame(
            [
                $a->GetId(),
                $b->GetId(),
            ],
            array_map(
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
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
        * @var array<int, DefinesOwnIntegerIdInterface>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithObject($b, true, null);

        static::assertSame(
            [
                $b->GetId(),
                $c->GetId(),
            ],
            array_map(
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DefinesOwnIntegerIdInterface>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithId($b->GetId(), true, null);

        static::assertSame(
            [
                $b->GetId(),
                $c->GetId(),
            ],
            array_map(
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
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
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DefinesOwnIntegerIdInterface>
        */
        $tree = $repo->RecallDaftNestedObjectPathToId($c->GetId(), true);

        static::assertSame(
            [
                $b->GetId(),
                $c->GetId(),
            ],
            array_map(
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
                },
                $tree
            )
        );

        static::assertSame(2, $repo->CountDaftNestedObjectPathToObject($c, true));
        static::assertSame(2, $repo->CountDaftNestedObjectPathToId($c->GetId(), true));

        /**
        * @var array<int, DefinesOwnIntegerIdInterface>
        */
        $tree = $repo->RecallDaftNestedObjectPathToObject($c, false);

        static::assertSame(
            [
                $b->GetId(),
            ],
            array_map(
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DefinesOwnIntegerIdInterface>
        */
        $tree = $repo->RecallDaftNestedObjectPathToId($c->GetId(), false);

        static::assertSame(
            [
                $b->GetId(),
            ],
            array_map(
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
                },
                $tree
            )
        );

        static::assertSame(1, $repo->CountDaftNestedObjectPathToObject($c, false));
        static::assertSame(1, $repo->CountDaftNestedObjectPathToId($c->GetId(), false));

        /**
        * @var array<int, DefinesOwnIntegerIdInterface>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithObject($b, false, null);

        static::assertSame(
            [
                $c->GetId(),
            ],
            array_map(
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DefinesOwnIntegerIdInterface>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithId($b->GetId(), false, null);

        static::assertSame(
            [
                $c->GetId(),
            ],
            array_map(
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
                },
                $tree
            )
        );

        static::assertSame(1, $repo->CountDaftNestedObjectTreeWithObject($b, false, null));
        static::assertSame(1, $repo->CountDaftNestedObjectTreeWithId($b->GetId(), false, null));

        /**
        * @var array<int, DefinesOwnIntegerIdInterface>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithObject($b, false, 0);

        static::assertSame(
            [
            ],
            array_map(
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
                },
                $tree
            )
        );

        /**
        * @var array<int, DefinesOwnIntegerIdInterface>
        */
        $tree = $repo->RecallDaftNestedObjectTreeWithId($b->GetId(), false, 0);

        static::assertSame(
            [
            ],
            array_map(
                function (DefinesOwnIntegerIdInterface $leaf) : int {
                    return $leaf->GetId();
                },
                $tree
            )
        );

        static::assertSame(0, $repo->CountDaftNestedObjectTreeWithObject($b, false, 0));
        static::assertSame(0, $repo->CountDaftNestedObjectTreeWithId($b->GetId(), false, 0));
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
