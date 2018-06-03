<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests;

use Closure;
use Generator;
use RuntimeException;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedWriteableIntObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftWriteableNestedObjectIntTree;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObjectTree;
use SignpostMarv\DaftObject\Tests\TestCase as Base;

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

        $a1 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($a0, 0);
        $b1 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($b0, 0);
        $c1 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($c0, 0);
        $d1 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($d0, 0);

        $a1 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($b0, $a0->GetId());
        $b1 = $repo->ModifyDaftNestedObjectTreeInsertBeforeId($a0, $b0->GetId());
        $c1 = $repo->ModifyDaftNestedObjectTreeInsertBelowId($c0, $b0->GetId());
        $d1 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($d0, $b0->GetId());

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

        $this->AssertTreeState(
            [0, 2, 4, 6],
            [1, 3, 5, 7],
            [0, 0, 0, 0],
            [
                $repo->ModifyDaftNestedObjectTreeInsertBelowId($a0, 0),
                $repo->ModifyDaftNestedObjectTreeInsertBelowId($b0, 0),
                $c0 = $repo->ModifyDaftNestedObjectTreeInsertBelowId($c0, 0),
                $repo->ModifyDaftNestedObjectTreeInsertBelowId($d0, 0),
            ]
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

    protected static function InitLeafClassInsertAfterId(
        DaftNestedWriteableObjectTree $repo,
        string $leafClass,
        int $afterId,
        array $ids
    ) : array {
        return array_map(
            function (int $id) use ($repo, $leafClass) : DaftNestedWriteableObject {
                return $repo->ModifyDaftNestedObjectTreeInsertAfterId(
                    static::InitLeafClass($leafClass, ['id' => $id]),
                    0
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
                return $repo->ModifyDaftNestedObjectTreeInsertAboveId(
                    static::InitLeafClass($leafClass, ['id' => $id]),
                    0
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
                return $repo->ModifyDaftNestedObjectTreeInsertBelowId(
                    static::InitLeafClass($leafClass, ['id' => $id]),
                    0
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
                    $testCase->AssertTreeStateFlipTwo(
                        false,
                        2,
                        1,
                        $repo,
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
                    return static::InitLeafClassInsertAboveId($repo, $leafClass, 0, [1, 2, 3, 4]);
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
                    return static::InitLeafClassInsertBelowId($repo, $leafClass, 0, [1, 2, 3, 4]);
                },
                function (
                    WriteableNestedTreeTest $testCase,
                    DaftNestedWriteableObjectTree $repo,
                    string $leafClass,
                    DaftNestedWriteableObject ...$leaves
                ) : void {
                    $leaves[1] = $repo->ModifyDaftNestedObjectTreeInsertAfterId(
                        $leaves[1]->GetId(),
                        $leaves[0]->GetId()
                    );

                    $testCase->AssertTreeState(
                        [0, 2, 4, 6],
                        [1, 3, 5, 7],
                        [0, 0, 0, 0],
                        $leaves
                    );

                    $leaves[1] = $repo->ModifyDaftNestedObjectTreeInsertAboveId(
                        $leaves[1]->GetId(),
                        $leaves[2]->GetId()
                    );
                    $c0 = $repo->RecallDaftObject($leaves[2]->GetId());

                    $this->assertInstanceOf($leafClass, $c0);

                    /**
                    * @var DaftNestedWriteableObject $c0
                    */
                    $c0 = $c0;

                    $leaves[2] = $c0;

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
                    $repo->ModifyDaftNestedObjectTreeInsertAboveId(1, 2);
                    /*
                    $repo->ModifyDaftNestedObjectTreeInsertAboveId(3, 4);
                    $repo->ModifyDaftNestedObjectTreeInsertAboveId(5, 6);
                    $repo->ModifyDaftNestedObjectTreeInsertAboveId(7, 8);
                    $repo->ModifyDaftNestedObjectTreeInsertAboveId(9, 10);
                    */

                    /**
                    * @var DaftNestedWriteableObject[] $tree
                    */
                    $tree = $repo->RecallDaftNestedObjectFullTree();

                    $this->AssertTreeState(
                        [0, 1, 4, 6, 8, 10, 12, 14, 16, 18],
                        [3, 2, 5, 7, 9, 11, 13, 15, 17, 19],
                        [0, 1, 0, 0, 0, 0, 0, 0, 0, 0],
                        $tree
                    );
                },
                [0, 2, 4, 6, 8, 10, 12, 14, 16, 18],
                [1, 3, 5, 7, 9, 11, 13, 15, 17, 19],
                [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            ],
        ];
    }

    /**
    * @param array<int, int> $left
    * @param array<int, int> $right
    * @param array<int, int> $level
    * @param array<int, DaftNestedWriteableObject> $leaves
    */
    protected function AssertTreeStateFlipTwo(
        bool $above,
        int $a,
        int $b,
        DaftNestedWriteableObjectTree $repo,
        array $left,
        array $right,
        array $level,
        array $leaves
    ) : void {
        /**
        * @var DaftNestedWriteableObject $leafA
        */
        $leafA =
            $above
                ? $repo->ModifyDaftNestedObjectTreeInsertAbove($leaves[$a], $leaves[$b])
                : $repo->ModifyDaftNestedObjectTreeInsertBelow($leaves[$a], $leaves[$b]);

        /**
        * @var DaftNestedWriteableObject $leafB
        */
        $leafB = $repo->RecallDaftObject($leaves[$b]->GetId());

        $leaves[$a] = $leafA;
        $leaves[$b] = $leafB;

        $this->AssertTreeState($left, $right, $level, $leaves);
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

        foreach ($left as $k => $v) {
            $this->assertInternalType('integer', $k);
            $this->assertTrue(isset($right[$k]));
            $this->assertInternalType('integer', $right[$k]);
            $this->assertTrue(isset($level[$k]));
            $this->assertInternalType('integer', $level[$k]);
            $this->assertTrue(isset($leaves[$k]));
            $this->assertInstanceOf(static::leafClass(), $leaves[$k]);

            $this->AssertLeafState($v, $right[$k], $level[$k], $leaves[$k]);
        }
    }

    protected function AssertLeafState(
        int $left,
        int $right,
        int $level,
        DaftNestedWriteableObject $leaf
    ) : void {
        $this->assertSame($left, $leaf->GetIntNestedLeft());
        $this->assertSame($right, $leaf->GetIntNestedRight());
        $this->assertSame($level, $leaf->GetIntNestedLevel());
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
