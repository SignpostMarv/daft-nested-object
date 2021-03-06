<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests;

use BadMethodCallException;
use Closure;
use Generator;
use InvalidArgumentException;
use RuntimeException;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedWriteableIntObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftWriteableNestedObjectIntTree;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObjectTree;
use SignpostMarv\DaftObject\DaftObjectRepository;
use SignpostMarv\DaftObject\DaftObjectRepository\Exceptions\DaftObjectNotRecalledException;
use SignpostMarv\DaftObject\SuitableForRepositoryType;

/**
* @template T as DaftNestedWriteableObject
* @template TRepo as DaftNestedWriteableObjectTree
*
* @template-extends NestedTreeTest<T, TRepo>
*/
class WriteableNestedTreeTest extends NestedTreeTest
{
    const LEFT_1to10 = [0, 2, 4, 6, 8, 10, 12, 14, 16, 18];
    const RIGHT_1to10 = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19];
    const LEVEL_1to10 = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

    const LEFT_DEDUPE = [0, 2, 3, 4, 8, 9, 11, 12, 16];
    const RIGHT_DEDUPE = [1, 7, 6, 5, 15, 10, 14, 13, 17];
    const LEVEL_DEDUPE = [0, 0, 1, 2, 0, 1, 1, 2, 0];

    /**
    * @dataProvider DataProviderArgs
    *
    * @param mixed ...$remainingTreeArgs
    *
    * @psalm-param class-string<TRepo> $treeClass
    * @psalm-param class-string<T> $leafClass
    */
    public function testTreeModification(
        string $treeClass,
        string $leafClass,
        ...$remainingTreeArgs
    ) : void {
        /**
        * @var DaftNestedWriteableObjectTree
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass, ...$remainingTreeArgs);

        $a0 = static::InitLeafClass($leafClass, ['id' => 1]);
        $b0 = static::InitLeafClass($leafClass, ['id' => 2]);
        $c0 = static::InitLeafClass($leafClass, ['id' => 3]);
        $d0 = static::InitLeafClass($leafClass, ['id' => 4]);

        if ($a0 instanceof DaftNestedWriteableIntObject) {
            static::assertSame([0], $a0->GetDaftNestedObjectParentId());

            $a0->SetDaftNestedObjectParentId([2]);

            static::assertSame([2], $a0->GetDaftNestedObjectParentId());

            $a0->SetIntNestedParentId(3);

            static::assertSame([3], $a0->GetDaftNestedObjectParentId());

            $repo_root = $repo->GetNestedObjectTreeRootId();

            static::assertSame([0], $repo_root);

            /**
            * @psalm-var array{0:int}
            */
            $repo_root = $repo_root;

            $a0->SetDaftNestedObjectParentId($repo_root);

            static::assertSame([0], $a0->GetDaftNestedObjectParentId());
        }

        $a1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($a0, 0, false, null);
        $b1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($b0, 0, false, null);
        $c1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($c0, 0, false, null);
        $d1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($d0, 0, false, null);

        static::assertNotSame($a0, $a1);
        static::assertNotSame($b0, $b1);
        static::assertNotSame($c0, $c1);
        static::assertNotSame($d0, $d1);

        $a1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($b0, $a0, false, null);
        $b1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($a0, $b0, true, null);
        $c1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($c0, $b0, false, false);
        $d1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($d0, $b0, false, null);

        static::assertNotSame($a0, $a1);
        static::assertNotSame($b0, $b1);
        static::assertNotSame($c0, $c1);
        static::assertNotSame($d0, $d1);

        $a1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($b0, $a0->GetId(), false, null);
        $b1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($a0, $b0->GetId(), true, null);
        $c1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($c0, $b0->GetId(), false, false);
        $d1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($d0, $b0->GetId(), false, null);

        static::assertNotSame($a0, $a1);
        static::assertNotSame($b0, $b1);
        static::assertNotSame($c0, $c1);
        static::assertNotSame($d0, $d1);

        $this->AssertTreeState(
            [0, 2, 4, 6],
            [1, 3, 5, 7],
            [0, 0, 0, 0],
            [$a0, $b0, $c0, $d0]
        );

        static::assertSame(
            $repo->GetNestedObjectTreeRootId(),
            $a0->GetDaftNestedObjectParentId()
        );
        static::assertSame(
            $repo->GetNestedObjectTreeRootId(),
            $b0->GetDaftNestedObjectParentId()
        );
        static::assertSame(
            $repo->GetNestedObjectTreeRootId(),
            $c0->GetDaftNestedObjectParentId()
        );
        static::assertSame(
            $repo->GetNestedObjectTreeRootId(),
            $d0->GetDaftNestedObjectParentId()
        );

        $repo->RememberDaftObject($a0);
        $repo->RememberDaftObject($b0);
        $repo->RememberDaftObject($c0);
        $repo->RememberDaftObject($d0);

        /**
        * @var DaftNestedWriteableObjectTree
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass, ...$remainingTreeArgs);

        $a0 = static::InitLeafClass($leafClass, ['id' => 1]);
        $b0 = static::InitLeafClass($leafClass, ['id' => 2]);
        $c0 = static::InitLeafClass($leafClass, ['id' => 3]);
        $d0 = static::InitLeafClass($leafClass, ['id' => 4]);

        $repo->ModifyDaftNestedObjectTreeInsertLoose($a0, 0, false, null);
        $repo->ModifyDaftNestedObjectTreeInsertLoose($b0, 0, false, null);
        $repo->ModifyDaftNestedObjectTreeInsertLoose($c0, 0, false, null);
        $repo->ModifyDaftNestedObjectTreeInsertLoose($d0, 0, false, null);

        list($a0, $b0, $c0, $d0) = $this->RecallFreshObjects($repo, $a0, $b0, $c0, $d0);

        $this->AssertTreeState(
            [0, 2, 4, 6],
            [1, 3, 5, 7],
            [0, 0, 0, 0],
            [$a0, $b0, $c0, $d0]
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithObject($c0, null);

        /**
        * @var DaftNestedWriteableObject
        */
        $a0 = $repo->RecallDaftObject($a0->GetId());

        /**
        * @var DaftNestedWriteableObject
        */
        $b0 = $repo->RecallDaftObject($b0->GetId());

        /**
        * @var DaftNestedWriteableObject
        */
        $d0 = $repo->RecallDaftObject($d0->GetId());

        $this->AssertTreeState(
            [0, 2, 4],
            [1, 3, 5],
            [0, 0, 0],
            [$a0, $b0, $d0]
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithObject($c0, null);

        $this->AssertTreeStateRecallLeaves(
            $repo,
            [0, 2, 4],
            [1, 3, 5],
            [0, 0, 0],
            [$a0, $b0, $d0]
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithId((array) $c0->GetId(), [0]);

        $this->AssertTreeStateRecallLeaves(
            $repo,
            [0, 2, 4],
            [1, 3, 5],
            [0, 0, 0],
            [$a0, $b0, $d0]
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithId((array) $c0->GetId(), [0]);

        /**
        * @var DaftNestedWriteableObject
        */
        $a0 = $repo->RecallDaftObject($a0->GetId());

        /**
        * @var DaftNestedWriteableObject
        */
        $b0 = $repo->RecallDaftObject($b0->GetId());

        /**
        * @var DaftNestedWriteableObject
        */
        $d0 = $repo->RecallDaftObject($d0->GetId());

        $this->AssertTreeState(
            [0, 2, 4],
            [1, 3, 5],
            [0, 0, 0],
            [$a0, $b0, $d0]
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithId((array) $b0->GetId(), [0]);

        /**
        * @var DaftNestedWriteableObject
        */
        $a0 = $repo->RecallDaftObject($a0->GetId());

        /**
        * @var DaftNestedWriteableObject
        */
        $d0 = $repo->RecallDaftObject($d0->GetId());

        $this->AssertTreeState(
            [0, 2],
            [1, 3],
            [0, 0],
            [$a0, $d0]
        );
    }

    /**
    * @dataProvider DataProviderArgsTreeRemovalFailure
    *
    * @param mixed ...$remainingTreeArgs
    *
    * @psalm-param class-string<TRepo> $treeClass
    * @psalm-param class-string<T> $leafClass
    */
    public function testTreeRemovalFailure(
        bool $byObject,
        string $treeClass,
        string $leafClass,
        ...$remainingTreeArgs
    ) : void {
        /**
        * @var DaftNestedWriteableObjectTree
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass, ...$remainingTreeArgs);

        $leaves = $this->setupTestTreeRemovalFailure($leafClass, $repo);

        if ($byObject) {
            $this->expectException(BadMethodCallException::class);
            $this->expectExceptionMessage('Cannot leave orphan objects in a tree');

            $repo->ModifyDaftNestedObjectTreeRemoveWithObject($leaves[0], null);
        } else {
            $this->expectException(DaftObjectNotRecalledException::class);
            $this->expectExceptionMessage(
                'Argument 1 passed to ' .
                DaftObjectRepository::class .
                '::RecallDaftObjectOrThrow() did not resolve to an instance of ' .
                $leafClass .
                ' from ' .
                get_class($repo) .
                '::RecallDaftObject()'
            );

            $repo->ModifyDaftNestedObjectTreeRemoveWithId((array) ($leaves[0]->GetId()), []);
        }
    }

    /**
    * @dataProvider DataProviderArgs
    *
    * @param mixed ...$remainingTreeArgs
    *
    * @psalm-param class-string<TRepo> $treeClass
    * @psalm-param class-string<T> $leafClass
    */
    public function testTreeRemovalFailureDueToOrphan(
        string $treeClass,
        string $leafClass,
        ...$remainingTreeArgs
    ) : void {
        if ( ! is_a($treeClass, DaftNestedWriteableObjectTree::class, true)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftNestedWriteableObjectTree::class .
                ', ' .
                $treeClass .
                ' given.'
            );
        }

        /**
        * @var DaftNestedWriteableObjectTree
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass, ...$remainingTreeArgs);

        $leaves = $this->setupTestTreeRemovalFailure($leafClass, $repo);

        $this->expectException(DaftObjectNotRecalledException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ' .
            DaftObjectRepository::class .
            '::RecallDaftObjectOrThrow() did not resolve to an instance of ' .
            $leafClass .
            ' from ' .
            get_class($repo) .
            '::RecallDaftObject()'
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithId((array) ($leaves[0]->GetId()), [11]);
    }

    /**
    * @psalm-return Generator<int, array{0:bool, 1:class-string<TRepo>, 2:class-string<T>}, mixed, void>
    */
    public function DataProviderArgsTreeRemovalFailure() : Generator
    {
        foreach ([true, false] as $bool) {
            /**
            * @var array
            */
            foreach ($this->DataProviderArgs() as $args) {
                array_unshift($args, $bool);

                /**
                * @psalm-var array{0:bool, 1:class-string<TRepo>, 2:class-string<T>}
                */
                $args = $args;

                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:Closure, 1:Closure, 2:array<int, int>, 3:array<int, int>, 4:array<int, int>, 5:class-string<TRepo>, 6:class-string<T>}, mixed, void>
    */
    public function DataProviderAdditionalTreeModification() : Generator
    {
        foreach ($this->DataProviderAdditionalArgs() as $additionalArgs) {
            foreach ($this->DataProviderArgs() as $args) {
                array_unshift(
                    $args,
                    $additionalArgs[0],
                    $additionalArgs[1],
                    $additionalArgs[2],
                    $additionalArgs[3],
                    $additionalArgs[4]
                );

                /**
                * @psalm-var array{0:Closure, 1:Closure, 2:array<int, int>, 3:array<int, int>, 4:array<int, int>, 5:class-string<TRepo>, 6:class-string<T>}
                */
                $args = $args;

                yield $args;
            }
        }
    }

    /**
    * @param array<int, int> $left
    * @param array<int, int> $right
    * @param array<int, int> $level
    * @param mixed ...$remainingTreeArgs
    *
    * @psalm-param class-string<TRepo> $treeClass
    * @psalm-param class-string<T> $leafClass
    *
    * @dataProvider DataProviderAdditionalTreeModification
    */
    public function testTreeModificationAdditionalArgs(
        Closure $setup,
        Closure $postAssert,
        array $left,
        array $right,
        array $level,
        string $treeClass,
        string $leafClass,
        ...$remainingTreeArgs
    ) : void {
        if ( ! is_a($treeClass, DaftNestedWriteableObjectTree::class, true)) {
            throw new InvalidArgumentException(
                'Argument 6 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftNestedWriteableObjectTree::class .
                ', ' .
                $treeClass .
                ' given.'
            );
        }

        /**
        * @var DaftNestedWriteableObjectTree
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass, ...$remainingTreeArgs);

        /**
        * @var array<int, DaftNestedWriteableObject>
        */
        $leaves = $setup($repo, $leafClass);

        $this->AssertTreeState($left, $right, $level, $leaves);

        $postAssert($repo, $leafClass, ...$leaves);

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Cannot modify leaf relative to itself!'
        );

        $repo->ModifyDaftNestedObjectTreeInsert($leaves[1], $leaves[1], false);
    }

    public static function InitLeafClassInsertAfterId(
        DaftNestedWriteableObjectTree $repo,
        string $leafClass,
        int $afterId,
        array $ids
    ) : array {
        return array_map(
            function (int $id) use ($repo, $leafClass, $afterId) : DaftNestedWriteableObject {
                return $repo->ModifyDaftNestedObjectTreeInsertLoose(
                    static::InitLeafClass($leafClass, ['id' => $id]),
                    $afterId,
                    false,
                    null
                );
            },
            $ids
        );
    }

    /**
    * @return array<int, DaftNestedWriteableObject>
    */
    protected function setupTestTreeRemovalFailure(
        string $leafClass,
        DaftNestedWriteableObjectTree $repo
    ) : array {
        /**
        * @var array<int, DaftNestedWriteableObject>
        */
        $leaves = static::InitLeafClassInsertAfterId($repo, $leafClass, 0, range(1, 10));

        /**
        * @var array<int, int>
        */
        $ids = range(1, 10);
        static::InsertLooseChunks($repo, false, true, ...$ids);
        static::InsertLooseChunks($repo, false, true, 1, 3, 5, 7);
        $repo->ModifyDaftNestedObjectTreeInsertLoose(10, 1, true, null);

        $leaves = $this->RecallFreshObjects($repo, ...$leaves);

        $this->AssertTreeState(
            [2, 3, 5, 6, 10, 11, 13, 14, 18, 0],
            [9, 4, 8, 7, 17, 12, 16, 15, 19, 1],
            [0, 1, 1, 2, 0, 1, 1, 2, 0, 0],
            $leaves
        );

        return $leaves;
    }

    /**
    * @psalm-return Generator<int, array{0:Closure, 1:Closure, 2:array<int, int>, 3:array<int, int>, 4:array<int, int>}, mixed, void>
    */
    protected function DataProviderAdditionalArgs() : Generator
    {
        yield from [
            [
                function (DaftNestedWriteableObjectTree $repo, string $leafClass) : array {
                    return static::InitLeafClassInsertAfterId($repo, $leafClass, 0, [1, 2, 3, 4]);
                },
                function (
                ) : void {
                },
                [0, 2, 4, 6],
                [1, 3, 5, 7],
                [0, 0, 0, 0],
            ],
            [
                function (DaftNestedWriteableObjectTree $repo, string $leafClass) : array {
                    return static::InitLeafClassInsertAfterId($repo, $leafClass, 0, [99, 2, 3, 4]);
                },
                function (
                    DaftNestedWriteableObjectTree $repo,
                    string $leafClass,
                    DaftNestedWriteableObject ...$leaves
                ) : void {
                    static::AssertTreeState(
                        [0, 2, 4, 6],
                        [1, 3, 5, 7],
                        [0, 0, 0, 0],
                        $leaves
                    );

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    $repo->ModifyDaftNestedObjectTreeInsert($leaves[1], $leaves[0], true);

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    $this->AssertTreeState(
                        [2, 0, 4, 6],
                        [3, 1, 5, 7],
                        [0, 0, 0, 0],
                        $leaves
                    );

                    $repo->ModifyDaftNestedObjectTreeInsert($leaves[1], $leaves[0], false);

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    $this->AssertTreeState(
                        [0, 2, 4, 6],
                        [1, 3, 5, 7],
                        [0, 0, 0, 0],
                        $leaves
                    );

                    $repo->ModifyDaftNestedObjectTreeInsert(
                        $leaves[0],
                        $leaves[1],
                        false,
                        true
                    );

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    $this->AssertTreeState(
                        [0, 1, 4, 6],
                        [3, 2, 5, 7],
                        [0, 1, 0, 0],
                        $leaves
                    );
                },
                [0, 2, 4, 6],
                [1, 3, 5, 7],
                [0, 0, 0, 0],
            ],
            [
                function (DaftNestedWriteableObjectTree $repo, string $leafClass) : array {
                    return static::InitLeafClassInsertAfterId($repo, $leafClass, 0, [1, 2, 3, 4]);
                },
                function (
                    DaftNestedWriteableObjectTree $repo,
                    string $leafClass,
                    DaftNestedWriteableObject ...$leaves
                ) : void {
                    static::AssertTreeState(
                        [0, 2, 4, 6],
                        [1, 3, 5, 7],
                        [0, 0, 0, 0],
                        $leaves
                    );

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    static::AssertTreeState(
                        [0, 2, 4, 6],
                        [1, 3, 5, 7],
                        [0, 0, 0, 0],
                        $leaves
                    );

                    $repo->ModifyDaftNestedObjectTreeInsert(
                        $leaves[1],
                        $leaves[2],
                        false,
                        true
                    );

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    $this->AssertTreeState(
                        [0, 2, 3, 6],
                        [1, 5, 4, 7],
                        [0, 0, 1, 0],
                        $leaves
                    );
                },
                [0, 2, 4, 6],
                [1, 3, 5, 7],
                [0, 0, 0, 0],
            ],
            [
                function (DaftNestedWriteableObjectTree $repo, string $leafClass) : array {
                    return static::InitLeafClassInsertAfterId($repo, $leafClass, 0, range(1, 10));
                },
                function (
                    DaftNestedWriteableObjectTree $repo
                ) : void {
                    /**
                    * @var array<int, int>
                    */
                    $ids = range(1, 10);
                    static::InsertLooseChunks($repo, false, true, ...$ids);

                    /**
                    * @var array<int, DaftNestedWriteableObject>
                    */
                    $tree = $repo->RecallDaftNestedObjectFullTree();

                    $this->AssertTreeState(
                        [0, 1, 4, 5, 8, 9, 12, 13, 16, 17],
                        [3, 2, 7, 6, 11, 10, 15, 14, 19, 18],
                        [0, 1, 0, 1, 0, 1, 0, 1, 0, 1],
                        $tree
                    );

                    $repo->ModifyDaftNestedObjectTreeInsertLoose(1, 3, false, true);
                    $repo->ModifyDaftNestedObjectTreeInsertLoose(5, 7, false, true);

                    /**
                    * @var array<int, DaftNestedWriteableObject>
                    */
                    $tree = $repo->RecallDaftNestedObjectFullTree();

                    $this->AssertTreeState(
                        [0, 1, 3, 4, 8, 9, 11, 12, 16, 17],
                        [7, 2, 6, 5, 15, 10, 14, 13, 19, 18],
                        [0, 1, 1, 2, 0, 1, 1, 2, 0, 1],
                        $tree
                    );

                    $repo->ModifyDaftNestedObjectTreeInsertLoose(10, 1, true, null);

                    $leaves = $this->RecallFreshObjects($repo, ...$tree);

                    $this->AssertTreeState(
                        [2, 3, 5, 6, 10, 11, 13, 14, 18, 0],
                        [9, 4, 8, 7, 17, 12, 16, 15, 19, 1],
                        [0, 1, 1, 2, 0, 1, 1, 2, 0, 0],
                        $leaves
                    );
                },
                self::LEFT_1to10,
                self::RIGHT_1to10,
                self::LEVEL_1to10,
            ],
            [
                function (DaftNestedWriteableObjectTree $repo, string $leafClass) : array {
                    return static::InitLeafClassInsertAfterId($repo, $leafClass, 0, range(1, 10));
                },
                function (
                    DaftNestedWriteableObjectTree $repo,
                    string $leafClass,
                    DaftNestedWriteableObject ...$leaves
                ) : void {
                    /**
                    * @var array<int, int>
                    */
                    $ids = array_merge(range(1, 10), [1, 3, 5, 7]);

                    static::InsertLooseChunks($repo, false, true, ...$ids);
                    $repo->ModifyDaftNestedObjectTreeInsertLoose(10, 1, true, null);

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    $this->AssertTreeState(
                        [2, 3, 5, 6, 10, 11, 13, 14, 18, 0],
                        [9, 4, 8, 7, 17, 12, 16, 15, 19, 1],
                        [0, 1, 1, 2, 0, 1, 1, 2, 0, 0],
                        $leaves
                    );

                    static::assertSame(
                        9,
                        $repo->ModifyDaftNestedObjectTreeRemoveWithId([2], [1])
                    );

                    /**
                    * @var array<int, DaftNestedWriteableObject>
                    */
                    $tree = $repo->RecallDaftNestedObjectFullTree();

                    $this->AssertTreeState(
                        self::LEFT_DEDUPE,
                        self::RIGHT_DEDUPE,
                        self::LEVEL_DEDUPE,
                        $tree
                    );
                },
                self::LEFT_1to10,
                self::RIGHT_1to10,
                self::LEVEL_1to10,
            ],
            [
                function (DaftNestedWriteableObjectTree $repo, string $leafClass) : array {
                    return static::InitLeafClassInsertAfterId($repo, $leafClass, 0, range(1, 10));
                },
                function (
                    DaftNestedWriteableObjectTree $repo
                ) : void {
                    /**
                    * @var array<int, int>
                    */
                    $ids = array_merge(range(1, 10), [1, 3, 5, 7]);

                    static::InsertLooseChunks($repo, false, true, ...$ids);
                    $repo->ModifyDaftNestedObjectTreeInsertLoose(10, 1, true, null);
                    $repo->ModifyDaftNestedObjectTreeRemoveWithId(
                        [2],
                        $repo->GetNestedObjectTreeRootId()
                    );

                    /**
                    * @var array<int, DaftNestedWriteableObject>
                    */
                    $tree = $repo->RecallDaftNestedObjectFullTree();

                    $this->AssertTreeState(
                        self::LEFT_DEDUPE,
                        self::RIGHT_DEDUPE,
                        self::LEVEL_DEDUPE,
                        $tree
                    );
                },
                self::LEFT_1to10,
                self::RIGHT_1to10,
                self::LEVEL_1to10,
            ],
            [
                function (DaftNestedWriteableObjectTree $repo, string $leafClass) : array {
                    return static::InitLeafClassInsertAfterId($repo, $leafClass, 0, range(1, 10));
                },
                function (
                    DaftNestedWriteableObjectTree $repo,
                    string $leafClass,
                    DaftNestedWriteableObject ...$leaves
                ) : void {
                    static::InsertLooseChunks($repo, false, true,
                        1, 2,
                        1, 3,
                        1, 4,
                        1, 5,
                        1, 6,
                        1, 7,
                        1, 8,
                        1, 9
                    );

                    $this->AssertTreeState(
                        [0, 1, 3, 5, 7, 9, 11, 13, 15, 18],
                        [17, 2, 4, 6, 8, 10, 12, 14, 16, 19],
                        [0, 1, 1, 1, 1, 1, 1, 1, 1, 0],
                        $this->RecallFreshObjects($repo, ...$leaves)
                    );

                    $repo->ModifyDaftNestedObjectTreeRemoveWithId(
                        [1],
                        $repo->GetNestedObjectTreeRootId()
                    );

                    /**
                    * @var array<int, DaftNestedWriteableObject>
                    */
                    $tree = $repo->RecallDaftNestedObjectFullTree();

                    /**
                    * @var array<int, int>
                    */
                    $left = range(0, 16, 2);

                    /**
                    * @var array<int, int>
                    */
                    $right = range(1, 17, 2);

                    /**
                    * @var array<int, int>
                    */
                    $level = array_fill(0, 9, 0);

                    $this->AssertTreeState($left, $right, $level, $tree);
                },
                self::LEFT_1to10,
                self::RIGHT_1to10,
                self::LEVEL_1to10,
            ],
            [
                function (DaftNestedWriteableObjectTree $repo, string $leafClass) : array {
                    return static::InitLeafClassInsertAfterId(
                        $repo,
                        $leafClass,
                        0,
                        [100, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    );
                },
                function (
                    DaftNestedWriteableObjectTree $repo,
                    string $leafClass,
                    DaftNestedWriteableObject ...$leaves
                ) : void {
                    static::InsertLooseChunks($repo, false, true,
                        100, 2,
                        100, 3,
                        100, 4,
                        100, 5,
                        100, 6,
                        100, 7,
                        100, 8,
                        100, 9
                    );

                    $left = [0, 1, 3, 5, 7, 9, 11, 13, 15, 18];
                    $right = [17, 2, 4, 6, 8, 10, 12, 14, 16, 19];
                    $level = [0, 1, 1, 1, 1, 1, 1, 1, 1, 0];

                    /**
                    * @var array<int, DaftNestedWriteableObject>
                    */
                    $tree = $repo->RecallDaftNestedObjectFullTree();

                    $this->AssertTreeState($left, $right, $level, $tree);

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    $repo->ModifyDaftNestedObjectTreeRemoveWithObject($leaves[0], $leaves[9]);

                    /**
                    * @var array<int, DaftNestedWriteableObject>
                    */
                    $tree = $repo->RecallDaftNestedObjectFullTree();

                    array_pop($left);
                    array_pop($right);
                    array_pop($level);

                    $this->AssertTreeState($left, $right, $level, $tree);
                },
                self::LEFT_1to10,
                self::RIGHT_1to10,
                self::LEVEL_1to10,
            ],
        ];
    }

    protected static function InsertLooseChunks(
        DaftNestedWriteableObjectTree $repo,
        bool $before,
        ? bool $above,
        int ...$ids
    ) : void {
        if (0 !== (count($ids) % 2)) {
            throw new InvalidArgumentException('ids must be of even length, not odd length!');
        }

        foreach (array_chunk($ids, 2) as $chunk) {
            list($a, $b) = $chunk;

            $repo->ModifyDaftNestedObjectTreeInsertLoose($a, $b, $before, $above);
        }
    }

    /**
    * @return array<int, DaftNestedWriteableObject>
    */
    protected function RecallFreshObjects(
        DaftNestedWriteableObjectTree $repo,
        DaftNestedWriteableObject ...$leaves
    ) : array {
        return array_map(
            function (DaftNestedWriteableObject $leaf) use ($repo) : DaftNestedWriteableObject {
                $out = $repo->RecallDaftObject($leaf->GetId());

                if ( ! ($out instanceof DaftNestedWriteableObject)) {
                    throw new RuntimeException('Could not retrieve fresh leaf from tree!');
                }

                return $out;
            },
            $leaves
        );
    }

    /**
    * @param array<int, int> $left
    * @param array<int, int> $right
    * @param array<int, int> $level
    */
    protected function AssertTreeStateRecallLeaves(
        DaftNestedWriteableObjectTree $repo,
        array $left,
        array $right,
        array $level,
        array $leaves
    ) : void {
        /**
        * @var array<int, DaftNestedWriteableObject>
        */
        $leaves = array_map(
            function (DaftNestedWriteableObject $leaf) use ($repo) : DaftNestedWriteableObject {
                $out = $repo->RecallDaftObject($leaf->GetId());

                if ( ! ($out instanceof DaftNestedWriteableObject)) {
                    throw new RuntimeException('Could not recall leaf from tree!');
                }

                return $out;
            },
            array_filter(
                $leaves,
                /**
                * @param mixed $maybe
                */
                function ($maybe) : bool {
                    return $maybe instanceof DaftNestedWriteableObject;
                }
            )
        );

        $this->AssertTreeState($left, $right, $level, $leaves);
    }

    /**
    * @param array<int, int> $left
    * @param array<int, int> $right
    * @param array<int, int> $level
    * @param array<int, DaftNestedWriteableObject> $leaves
    */
    protected function AssertTreeState(
        array $left,
        array $right,
        array $level,
        array $leaves
    ) : void {
        static::assertCount(count($left), $right);
        static::assertCount(count($right), $leaves);
        static::assertCount(count($leaves), $level);

        $actualLeft = [];
        $actualRight = [];
        $actualLevel = [];

        foreach ($leaves as $leaf) {
            $actualLeft[] = $leaf->GetIntNestedLeft();
            $actualRight[] = $leaf->GetIntNestedRight();
            $actualLevel[] = $leaf->GetIntNestedLevel();
        }

        static::assertSame(
            [
                'left' => $left,
                'right' => $right,
                'level' => $level,
            ],
            [
                'left' => $actualLeft,
                'right' => $actualRight,
                'level' => $actualLevel,
            ]
        );
    }

    /**
    * @param mixed ...$additionalArgs
    *
    * @return DaftNestedWriteableObject&SuitableForRepositoryType
    */
    protected static function InitLeafClass(
        string $type,
        array $cargs = [],
        ...$additionalArgs
    ) : DaftNestedWriteableObject {
        if ( ! is_a($type, DaftNestedWriteableObject::class, true)) {
            throw new RuntimeException(
                'Leaf class was not an implementation of ' .
                DaftNestedWriteableObject::class
            );
        }

        $cargs = array_merge(
            [
                'intNestedLeft' => 0,
                'intNestedRight' => 0,
                'intNestedLevel' => 0,
                'intNestedParentId' => 0,
                'intNestedSortOrder' => 0,
            ],
            $cargs
        );

        $args = array_values($additionalArgs);
        array_unshift($args, $cargs);

        return new $type(...$args);
    }

    protected static function leafClass() : string
    {
        return DaftNestedWriteableIntObject::class;
    }

    protected static function treeClass() : string
    {
        return DaftWriteableNestedObjectIntTree::class;
    }
}
