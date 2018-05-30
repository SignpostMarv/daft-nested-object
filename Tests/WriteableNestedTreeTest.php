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

        $this->assertSame(0, $a0->GetIntNestedLeft());
        $this->assertSame(0, $b0->GetIntNestedLeft());
        $this->assertSame(0, $c0->GetIntNestedLeft());
        $this->assertSame(0, $d0->GetIntNestedLeft());

        $this->assertSame(0, $a0->GetIntNestedRight());
        $this->assertSame(0, $b0->GetIntNestedRight());
        $this->assertSame(0, $c0->GetIntNestedRight());
        $this->assertSame(0, $d0->GetIntNestedRight());

        $this->assertSame(0, $a0->GetIntNestedLevel());
        $this->assertSame(0, $b0->GetIntNestedLevel());
        $this->assertSame(0, $c0->GetIntNestedLevel());
        $this->assertSame(0, $d0->GetIntNestedLevel());

        $this->assertSame(0, $a0->GetIntNestedParentId());
        $this->assertSame(0, $b0->GetIntNestedParentId());
        $this->assertSame(0, $c0->GetIntNestedParentId());
        $this->assertSame(0, $d0->GetIntNestedParentId());

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

        $this->assertSame(0, $a0->GetIntNestedLeft());
        $this->assertSame(2, $b0->GetIntNestedLeft());
        $this->assertSame(4, $c0->GetIntNestedLeft());
        $this->assertSame(6, $d0->GetIntNestedLeft());

        $this->assertSame(1, $a0->GetIntNestedRight());
        $this->assertSame(3, $b0->GetIntNestedRight());
        $this->assertSame(5, $c0->GetIntNestedRight());
        $this->assertSame(7, $d0->GetIntNestedRight());

        $this->assertSame(0, $a0->GetIntNestedLevel());
        $this->assertSame(0, $b0->GetIntNestedLevel());
        $this->assertSame(0, $c0->GetIntNestedLevel());
        $this->assertSame(0, $d0->GetIntNestedLevel());

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

        $this->assertSame(0, $a0->GetIntNestedLeft());
        $this->assertSame(2, $b0->GetIntNestedLeft());
        $this->assertSame(4, $c0->GetIntNestedLeft());
        $this->assertSame(6, $d0->GetIntNestedLeft());

        $this->assertSame(1, $a0->GetIntNestedRight());
        $this->assertSame(3, $b0->GetIntNestedRight());
        $this->assertSame(5, $c0->GetIntNestedRight());
        $this->assertSame(7, $d0->GetIntNestedRight());

        $this->assertSame(0, $a0->GetIntNestedLevel());
        $this->assertSame(0, $b0->GetIntNestedLevel());
        $this->assertSame(0, $c0->GetIntNestedLevel());
        $this->assertSame(0, $d0->GetIntNestedLevel());

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

        $this->assertSame(0, $a0->GetIntNestedLeft());
        $this->assertSame(2, $b0->GetIntNestedLeft());
        $this->assertSame(4, $c0->GetIntNestedLeft());
        $this->assertSame(6, $d0->GetIntNestedLeft());

        $this->assertSame(1, $a0->GetIntNestedRight());
        $this->assertSame(3, $b0->GetIntNestedRight());
        $this->assertSame(5, $c0->GetIntNestedRight());
        $this->assertSame(7, $d0->GetIntNestedRight());

        $this->assertSame(0, $a0->GetIntNestedLevel());
        $this->assertSame(0, $b0->GetIntNestedLevel());
        $this->assertSame(0, $c0->GetIntNestedLevel());

        $c0 = $repo->ModifyDaftNestedObjectTreeInsertBelow($c0, $b0);
        $b0 = $repo->RecallDaftObject($b0->GetId());

        $this->assertSame(0, $a0->GetIntNestedLeft());
        $this->assertSame(2, $b0->GetIntNestedLeft());
        $this->assertSame(3, $c0->GetIntNestedLeft());
        $this->assertSame(6, $d0->GetIntNestedLeft());

        $this->assertSame(1, $a0->GetIntNestedRight());
        $this->assertSame(5, $b0->GetIntNestedRight());
        $this->assertSame(4, $c0->GetIntNestedRight());
        $this->assertSame(7, $d0->GetIntNestedRight());

        $this->assertSame(0, $a0->GetIntNestedLevel());
        $this->assertSame(0, $b0->GetIntNestedLevel());
        $this->assertSame(1, $c0->GetIntNestedLevel());
        $this->assertSame(0, $d0->GetIntNestedLevel());

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

        $this->assertSame(0, $a0->GetIntNestedLeft());
        $this->assertSame(2, $b0->GetIntNestedLeft());
        $this->assertSame(4, $c0->GetIntNestedLeft());
        $this->assertSame(6, $d0->GetIntNestedLeft());

        $this->assertSame(1, $a0->GetIntNestedRight());
        $this->assertSame(3, $b0->GetIntNestedRight());
        $this->assertSame(5, $c0->GetIntNestedRight());
        $this->assertSame(7, $d0->GetIntNestedRight());

        $this->assertSame(0, $a0->GetIntNestedLevel());
        $this->assertSame(0, $b0->GetIntNestedLevel());
        $this->assertSame(0, $c0->GetIntNestedLevel());
        $this->assertSame(0, $d0->GetIntNestedLevel());

        $b0 = $repo->ModifyDaftNestedObjectTreeInsertAfter($b0, $a0);

        $this->assertSame(0, $a0->GetIntNestedLeft());
        $this->assertSame(2, $b0->GetIntNestedLeft());
        $this->assertSame(4, $c0->GetIntNestedLeft());
        $this->assertSame(6, $d0->GetIntNestedLeft());

        $this->assertSame(1, $a0->GetIntNestedRight());
        $this->assertSame(3, $b0->GetIntNestedRight());
        $this->assertSame(5, $c0->GetIntNestedRight());
        $this->assertSame(7, $d0->GetIntNestedRight());

        $this->assertSame(0, $a0->GetIntNestedLevel());
        $this->assertSame(0, $b0->GetIntNestedLevel());
        $this->assertSame(0, $c0->GetIntNestedLevel());
        $this->assertSame(0, $d0->GetIntNestedLevel());

        $b0 = $repo->ModifyDaftNestedObjectTreeInsertAbove($b0, $c0);
        $c0 = $repo->RecallDaftObject($c0->GetId());

        $this->assertSame(0, $a0->GetIntNestedLeft());
        $this->assertSame(2, $b0->GetIntNestedLeft());
        $this->assertSame(3, $c0->GetIntNestedLeft());
        $this->assertSame(6, $d0->GetIntNestedLeft());

        $this->assertSame(1, $a0->GetIntNestedRight());
        $this->assertSame(5, $b0->GetIntNestedRight());
        $this->assertSame(4, $c0->GetIntNestedRight());
        $this->assertSame(7, $d0->GetIntNestedRight());

        $this->assertSame(0, $a0->GetIntNestedLevel());
        $this->assertSame(0, $b0->GetIntNestedLevel());
        $this->assertSame(1, $c0->GetIntNestedLevel());
        $this->assertSame(0, $d0->GetIntNestedLevel());

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

        $this->assertSame(0, $a0->GetIntNestedLeft());
        $this->assertSame(2, $b0->GetIntNestedLeft());
        $this->assertSame(4, $c0->GetIntNestedLeft());
        $this->assertSame(6, $d0->GetIntNestedLeft());

        $this->assertSame(1, $a0->GetIntNestedRight());
        $this->assertSame(3, $b0->GetIntNestedRight());
        $this->assertSame(5, $c0->GetIntNestedRight());
        $this->assertSame(7, $d0->GetIntNestedRight());

        $this->assertSame(0, $a0->GetIntNestedLevel());
        $this->assertSame(0, $b0->GetIntNestedLevel());
        $this->assertSame(0, $c0->GetIntNestedLevel());
        $this->assertSame(0, $d0->GetIntNestedLevel());

        $b0 = $repo->ModifyDaftNestedObjectTreeInsertAfterId($b0->GetId(), $a0->GetId());

        $this->assertSame(0, $a0->GetIntNestedLeft());
        $this->assertSame(2, $b0->GetIntNestedLeft());
        $this->assertSame(4, $c0->GetIntNestedLeft());
        $this->assertSame(6, $d0->GetIntNestedLeft());

        $this->assertSame(1, $a0->GetIntNestedRight());
        $this->assertSame(3, $b0->GetIntNestedRight());
        $this->assertSame(5, $c0->GetIntNestedRight());
        $this->assertSame(7, $d0->GetIntNestedRight());

        $this->assertSame(0, $a0->GetIntNestedLevel());
        $this->assertSame(0, $b0->GetIntNestedLevel());
        $this->assertSame(0, $c0->GetIntNestedLevel());
        $this->assertSame(0, $d0->GetIntNestedLevel());

        $b0 = $repo->ModifyDaftNestedObjectTreeInsertAboveId($b0->GetId(), $c0->GetId());
        $c0 = $repo->RecallDaftObject($c0->GetId());

        $this->assertSame(0, $a0->GetIntNestedLeft());
        $this->assertSame(2, $b0->GetIntNestedLeft());
        $this->assertSame(3, $c0->GetIntNestedLeft());
        $this->assertSame(6, $d0->GetIntNestedLeft());

        $this->assertSame(1, $a0->GetIntNestedRight());
        $this->assertSame(5, $b0->GetIntNestedRight());
        $this->assertSame(4, $c0->GetIntNestedRight());
        $this->assertSame(7, $d0->GetIntNestedRight());

        $this->assertSame(0, $a0->GetIntNestedLevel());
        $this->assertSame(0, $b0->GetIntNestedLevel());
        $this->assertSame(1, $c0->GetIntNestedLevel());
        $this->assertSame(0, $d0->GetIntNestedLevel());

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

        $this->AssertTreeState(
            [0, 2, 4],
            [1, 3, 5],
            [0, 0, 0],
            [
                $repo->RecallDaftObject($a0->GetId()),
                $repo->RecallDaftObject($b0->GetId()),
                $repo->RecallDaftObject($d0->GetId()),
            ]
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithObject($c0, null);

        $this->AssertTreeState(
            [0, 2, 4],
            [1, 3, 5],
            [0, 0, 0],
            [
                $repo->RecallDaftObject($a0->GetId()),
                $repo->RecallDaftObject($b0->GetId()),
                $repo->RecallDaftObject($d0->GetId()),
            ]
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithId($c0->GetId(), null);

        $this->AssertTreeState(
            [0, 2, 4],
            [1, 3, 5],
            [0, 0, 0],
            [
                $repo->RecallDaftObject($a0->GetId()),
                $repo->RecallDaftObject($b0->GetId()),
                $repo->RecallDaftObject($d0->GetId()),
            ]
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithId($c0->GetId(), null);

        $this->AssertTreeState(
            [0, 2, 4],
            [1, 3, 5],
            [0, 0, 0],
            [
                $repo->RecallDaftObject($a0->GetId()),
                $repo->RecallDaftObject($b0->GetId()),
                $repo->RecallDaftObject($d0->GetId()),
            ]
        );

        $repo->ModifyDaftNestedObjectTreeRemoveWithId($b0->GetId(), null);

        $this->AssertTreeState(
            [0, 2],
            [1, 3],
            [0, 0],
            [
                $repo->RecallDaftObject($a0->GetId()),
                $repo->RecallDaftObject($d0->GetId()),
            ]
        );
    }

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

    protected static function InitLeafClass(string $type, array $cargs = [], ... $additionalArgs) : DaftNestedWriteableObject
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
