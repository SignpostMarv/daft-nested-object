<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests;

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

        $a0 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($a0, 0);
        $b0 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($b0, 0);
        $c0 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($c0, 0);
        $d0 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($d0, 0);

        $this->AssertTreeState(
            [0, 2, 4, 6],
            [1, 3, 5, 7],
            [0, 0, 0, 0],
            [$a0, $b0, $c0, $d0]
        );

        $this->AssertTreeStateFlipTwo(
            false,
            2,
            1,
            $repo,
            [0, 2, 3, 6],
            [1, 5, 4, 7],
            [0, 0, 1, 0],
            [$a0, $b0, $c0, $d0]
        );

        /**
        * @var DaftNestedWriteableObjectTree $repo
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass);

        $a0 = static::InitLeafClass($leafClass, ['id' => 1]);
        $b0 = static::InitLeafClass($leafClass, ['id' => 2]);
        $c0 = static::InitLeafClass($leafClass, ['id' => 3]);
        $d0 = static::InitLeafClass($leafClass, ['id' => 4]);

        $a0 = $repo->ModifyDaftNestedObjectTreeInsertAboveId($a0, 0);
        $b0 = $repo->ModifyDaftNestedObjectTreeInsertAboveId($b0, 0);
        $c0 = $repo->ModifyDaftNestedObjectTreeInsertAboveId($c0, 0);
        $d0 = $repo->ModifyDaftNestedObjectTreeInsertAboveId($d0, 0);

        $this->AssertTreeState(
            [0, 2, 4, 6],
            [1, 3, 5, 7],
            [0, 0, 0, 0],
            [$a0, $b0, $c0, $d0]
        );

        /**
        * @var DaftNestedWriteableObjectTree $repo
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass);

        $a0 = static::InitLeafClass($leafClass, ['id' => 1]);
        $b0 = static::InitLeafClass($leafClass, ['id' => 2]);
        $c0 = static::InitLeafClass($leafClass, ['id' => 3]);
        $d0 = static::InitLeafClass($leafClass, ['id' => 4]);

        $a0 = $repo->ModifyDaftNestedObjectTreeInsertBelowId($a0, 0);
        $b0 = $repo->ModifyDaftNestedObjectTreeInsertBelowId($b0, 0);
        $c0 = $repo->ModifyDaftNestedObjectTreeInsertBelowId($c0, 0);
        $d0 = $repo->ModifyDaftNestedObjectTreeInsertBelowId($d0, 0);

        $this->AssertTreeState(
            [0, 2, 4, 6],
            [1, 3, 5, 7],
            [0, 0, 0, 0],
            [$a0, $b0, $c0, $d0]
        );

        $b0 = $repo->ModifyDaftNestedObjectTreeInsertAfter($b0, $a0);

        $this->AssertTreeState(
            [0, 2, 4, 6],
            [1, 3, 5, 7],
            [0, 0, 0, 0],
            [$a0, $b0, $c0, $d0]
        );

        $this->AssertTreeStateFlipTwo(
            true,
            1,
            2,
            $repo,
            [0, 2, 3, 6],
            [1, 5, 4, 7],
            [0, 0, 1, 0],
            [$a0, $b0, $c0, $d0]
        );

        /**
        * @var DaftNestedWriteableObjectTree $repo
        */
        $repo = $treeClass::DaftObjectRepositoryByType($leafClass);

        $a0 = static::InitLeafClass($leafClass, ['id' => 1]);
        $b0 = static::InitLeafClass($leafClass, ['id' => 2]);
        $c0 = static::InitLeafClass($leafClass, ['id' => 3]);
        $d0 = static::InitLeafClass($leafClass, ['id' => 4]);

        $a0 = $repo->ModifyDaftNestedObjectTreeInsertBelowId($a0, 0);
        $b0 = $repo->ModifyDaftNestedObjectTreeInsertBelowId($b0, 0);
        $c0 = $repo->ModifyDaftNestedObjectTreeInsertBelowId($c0, 0);
        $d0 = $repo->ModifyDaftNestedObjectTreeInsertBelowId($d0, 0);

        $this->AssertTreeState(
            [0, 2, 4, 6],
            [1, 3, 5, 7],
            [0, 0, 0, 0],
            [$a0, $b0, $c0, $d0]
        );

        $b0 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($b0->GetId(), $a0->GetId());

        $this->AssertTreeState(
            [0, 2, 4, 6],
            [1, 3, 5, 7],
            [0, 0, 0, 0],
            [$a0, $b0, $c0, $d0]
        );

        $b0 = $repo->ModifyDaftNestedObjectTreeInsertAboveId($b0->GetId(), $c0->GetId());
        $c0 = $repo->RecallDaftObject($c0->GetId());

        $this->assertInstanceOf($leafClass, $c0);

        /**
        * @var DaftNestedWriteableObject $c0
        */
        $c0 = $c0;

        $this->AssertTreeState(
            [0, 2, 3, 6],
            [1, 5, 4, 7],
            [0, 0, 1, 0],
            [$a0, $b0, $c0, $d0]
        );

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
