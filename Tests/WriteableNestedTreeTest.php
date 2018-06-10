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

class WriteableNestedTreeTest extends NestedTreeTest
{
    /**
    * @dataProvider DataProviderArgs
    */
    public function testTreeModification(string $leafClass, string $treeClass) : void
    {
        $this->assertTrue(is_a($leafClass, DaftNestedWriteableObject::class, true));
        $this->assertTrue(is_a($treeClass, DaftNestedWriteableObjectTree::class, true));

        /**
        * @var DaftNestedWriteableObjectTree $repo
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass);

        $a0 = static::InitLeafClass($leafClass, ['id' => 1]);
        $b0 = static::InitLeafClass($leafClass, ['id' => 2]);
        $c0 = static::InitLeafClass($leafClass, ['id' => 3]);
        $d0 = static::InitLeafClass($leafClass, ['id' => 4]);

        $a1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($a0, 0, false, null);
        $b1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($b0, 0, false, null);
        $c1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($c0, 0, false, null);
        $d1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($d0, 0, false, null);

        $a1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($b0, $a0->GetId(), false, null);
        $b1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($a0, $b0->GetId(), true, null);
        $c1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($c0, $b0->GetId(), false, false);
        $d1 = $repo->ModifyDaftNestedObjectTreeInsertLoose($d0, $b0->GetId(), false, null);

        $this->assertNotSame($a0, $a1);
        $this->assertNotSame($b0, $b1);
        $this->assertNotSame($c0, $c1);
        $this->assertNotSame($d0, $d1);

        $this->AssertTreeState(
            [0, 0, 0, 0],
            [0, 0, 0, 0],
            [0, 0, 0, 0],
            [$a0, $b0, $c0, $d0]
        );

        $this->assertSame(
            (array) $repo->GetNestedObjectTreeRootId(),
            $a0->ObtainDaftNestedObjectParentId()
        );
        $this->assertSame(
            (array) $repo->GetNestedObjectTreeRootId(),
            $b0->ObtainDaftNestedObjectParentId()
        );
        $this->assertSame(
            (array) $repo->GetNestedObjectTreeRootId(),
            $c0->ObtainDaftNestedObjectParentId()
        );
        $this->assertSame(
            (array) $repo->GetNestedObjectTreeRootId(),
            $d0->ObtainDaftNestedObjectParentId()
        );

        $repo->RememberDaftObject($a0);
        $repo->RememberDaftObject($b0);
        $repo->RememberDaftObject($c0);
        $repo->RememberDaftObject($d0);

        /**
        * @var DaftNestedWriteableObjectTree $repo
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass);

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
        * @var DaftNestedWriteableObject $a0
        */
        $a0 = $repo->RecallDaftObject($a0->GetId());

        /**
        * @var DaftNestedWriteableObject $b0
        */
        $b0 = $repo->RecallDaftObject($b0->GetId());

        /**
        * @var DaftNestedWriteableObject $d0
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

        $repo->ModifyDaftNestedObjectTreeRemoveWithId($c0->GetId(), null);

        $this->AssertTreeStateRecallLeaves(
            $repo,
            [0, 2, 4],
            [1, 3, 5],
            [0, 0, 0],
            [$a0, $b0, $d0]
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithId($c0->GetId(), null);

        /**
        * @var DaftNestedWriteableObject $a0
        */
        $a0 = $repo->RecallDaftObject($a0->GetId());

        /**
        * @var DaftNestedWriteableObject $b0
        */
        $b0 = $repo->RecallDaftObject($b0->GetId());

        /**
        * @var DaftNestedWriteableObject $d0
        */
        $d0 = $repo->RecallDaftObject($d0->GetId());

        $this->AssertTreeState(
            [0, 2, 4],
            [1, 3, 5],
            [0, 0, 0],
            [$a0, $b0, $d0]
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithId($b0->GetId(), null);

        /**
        * @var DaftNestedWriteableObject $a0
        */
        $a0 = $repo->RecallDaftObject($a0->GetId());

        /**
        * @var DaftNestedWriteableObject $d0
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
    * @dataProvider DataProviderArgs
    */
    public function testTreeRemovalFailure(string $leafClass, string $treeClass) : void
    {
        /**
        * @var DaftNestedWriteableObjectTree $repo
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass);

        /**
        * @var array<int, DaftNestedWriteableObject> $leaves
        */
        $leaves = static::InitLeafClassInsertAfterId($repo, $leafClass, 0, range(1, 10));

        /**
        * @var array<int, int> $ids
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

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot leave orphan objects in a tree');

        $repo->ModifyDaftNestedObjectTreeRemoveWithObject($leaves[0], null);
    }

    public function DataProviderAdditionalTreeModification() : Generator
    {
        /**
        * @var string[] $args
        */
        foreach ($this->DataProviderArgs() as $args) {
            /**
            * @var array $additionalArgs
            */
            foreach ($this->DataProviderAdditionalArgs() as $additionalArgs) {
                array_unshift($additionalArgs, ...$args);

                yield $additionalArgs;
            }
        }
    }

    /**
    * @param array<int, int> $left
    * @param array<int, int> $right
    * @param array<int, int> $level
    *
    * @dataProvider DataProviderAdditionalTreeModification
    */
    public function testTreeModificationAdditionalArgs(
        string $leafClass,
        string $treeClass,
        Closure $setup,
        Closure $postAssert,
        array $left,
        array $right,
        array $level
    ) : void {
        /**
        * @var DaftNestedWriteableObjectTree $repo
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass);

        /**
        * @var array<int, DaftNestedWriteableObject> $leaves
        */
        $leaves = $setup($repo, $leafClass);

        $this->AssertTreeState($left, $right, $level, $leaves);

        $postAssert($this, $repo, $leafClass, ...$leaves);
    }

    public static function InitLeafClassInsertAfterId(
        DaftNestedWriteableObjectTree $repo,
        string $leafClass,
        int $afterId,
        array $ids
    ) : array {
        return array_map(
            function (int $id) use ($repo, $leafClass) : DaftNestedWriteableObject {
                return $repo->ModifyDaftNestedObjectTreeInsertLoose(
                    static::InitLeafClass($leafClass, ['id' => $id]),
                    0,
                    false,
                    null
                );
            },
            $ids
        );
    }

    protected static function InitLeafClassInsertAboveId(
        DaftNestedWriteableObjectTree $repo,
        string $leafClass,
        int $afterId,
        array $ids
    ) : array {
        return array_map(
            function (int $id) use ($repo, $leafClass) : DaftNestedWriteableObject {
                return $repo->ModifyDaftNestedObjectTreeInsertLoose(
                    static::InitLeafClass($leafClass, ['id' => $id]),
                    0,
                    false,
                    true
                );
            },
            $ids
        );
    }

    protected static function InitLeafClassInsertBelowId(
        DaftNestedWriteableObjectTree $repo,
        string $leafClass,
        int $afterId,
        array $ids
    ) : array {
        return array_map(
            function (int $id) use ($repo, $leafClass) : DaftNestedWriteableObject {
                return $repo->ModifyDaftNestedObjectTreeInsertLoose(
                    static::InitLeafClass($leafClass, ['id' => $id]),
                    0,
                    false,
                    false
                );
            },
            $ids
        );
    }

    protected function DataProviderAdditionalArgs() : Generator
    {
        yield from [
            [
                function (DaftNestedWriteableObjectTree $repo, string $leafClass) : array {
                    return static::InitLeafClassInsertAfterId($repo, $leafClass, 0, [1, 2, 3, 4]);
                },
                function (
                    WriteableNestedTreeTest $testCase,
                    DaftNestedWriteableObjectTree $repo,
                    string $leafClass,
                    DaftNestedWriteableObject ...$leaves
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
                    WriteableNestedTreeTest $testCase,
                    DaftNestedWriteableObjectTree $repo,
                    string $leafClass,
                    DaftNestedWriteableObject ...$leaves
                ) : void {
                    $testCase->AssertTreeState(
                        [0, 2, 4, 6],
                        [1, 3, 5, 7],
                        [0, 0, 0, 0],
                        $leaves
                    );

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    $leaf = $repo->ModifyDaftNestedObjectTreeInsert($leaves[1], $leaves[0], true);

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    $this->AssertTreeState(
                        [2, 0, 4, 6],
                        [3, 1, 5, 7],
                        [0, 0, 0, 0],
                        $leaves
                    );

                    $leaf = $repo->ModifyDaftNestedObjectTreeInsert($leaves[1], $leaves[0], false);

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    $this->AssertTreeState(
                        [0, 2, 4, 6],
                        [1, 3, 5, 7],
                        [0, 0, 0, 0],
                        $leaves
                    );

                    $leaf = $repo->ModifyDaftNestedObjectTreeInsert($leaves[0], $leaves[1], false, true);

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
                    WriteableNestedTreeTest $testCase,
                    DaftNestedWriteableObjectTree $repo,
                    string $leafClass,
                    DaftNestedWriteableObject ...$leaves
                ) : void {
                    $testCase->AssertTreeState(
                        [0, 2, 4, 6],
                        [1, 3, 5, 7],
                        [0, 0, 0, 0],
                        $leaves
                    );

                    $leaves = $this->RecallFreshObjects($repo, ...$leaves);

                    $testCase->AssertTreeState(
                        [0, 2, 4, 6],
                        [1, 3, 5, 7],
                        [0, 0, 0, 0],
                        $leaves
                    );

                    $leaf = $repo->ModifyDaftNestedObjectTreeInsert($leaves[1], $leaves[2], false, true);

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
                    WriteableNestedTreeTest $testCase,
                    DaftNestedWriteableObjectTree $repo,
                    string $leafClass,
                    DaftNestedWriteableObject ...$leaves
                ) : void {
                    /**
                    * @var array<int, int> $ids
                    */
                    $ids = range(1, 10);
                    static::InsertLooseChunks($repo, false, true, ...$ids);

                    /**
                    * @var array<int, DaftNestedWriteableObject> $tree
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
                    * @var array<int, DaftNestedWriteableObject> $tree
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
                [0, 2, 4, 6, 8, 10, 12, 14, 16, 18],
                [1, 3, 5, 7, 9, 11, 13, 15, 17, 19],
                [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
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
    * @param array<int, DaftNestedWriteableObject> $leaves
    */
    protected function AssertTreeStateRecallLeaves(
        DaftNestedWriteableObjectTree $repo,
        array $left,
        array $right,
        array $level,
        array $leaves
    ) : void {
        /**
        * @var array<int, DaftNestedWriteableObject> $leaves
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
        $this->assertSame(count($left), count($right));
        $this->assertSame(count($right), count($leaves));
        $this->assertSame(count($leaves), count($level));

        $actualLeft = [];
        $actualRight = [];
        $actualLevel = [];

        foreach ($leaves as $leaf) {
            $actualLeft[] = $leaf->GetIntNestedLeft();
            $actualRight[] = $leaf->GetIntNestedRight();
            $actualLevel[] = $leaf->GetIntNestedLevel();
        }

        $this->assertSame(
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

    protected function AssertLeafState(
        int $left,
        int $right,
        int $level,
        DaftNestedWriteableObject $leaf
    ) : void {
        $this->assertSame($left, $leaf->GetIntNestedLeft(), sprintf(
            'Left does not match state %u (%u), %u, %u',
            $left,
            $leaf->GetIntNestedLeft(),
            $right,
            $level
        ));
        $this->assertSame($right, $leaf->GetIntNestedRight(), sprintf(
            'Right does not match state %u, %u (%u), %u',
            $left,
            $right,
            $leaf->GetIntNestedRight(),
            $level
        ));
        $this->assertSame($level, $leaf->GetIntNestedLevel(), sprintf(
            'Level does not match state %u, %u, %u (%u)',
            $left,
            $right,
            $level,
            $leaf->GetIntNestedLevel()
        ));
    }

    protected static function InitLeafClass(string $type, array $cargs = [], ...$additionalArgs) : DaftNestedWriteableObject
    {
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
