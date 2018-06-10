<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use SignpostMarv\DaftObject\DaftWriteableObjectMemoryTree;
use SignpostMarv\DaftObject\Tests\TestCase as Base;

class CoverageTest extends Base
{
    public function testRecallDaftNestedObjectFullTreeCouldNotRetrieve() : void
    {
        /**
        * @var Fixtures\ThrowingMemoryTree $repo
        */
        $repo = Fixtures\ThrowingMemoryTree::DaftObjectRepositoryByType(
            Fixtures\DaftNestedIntObject::class
        );

        $leaf = new Fixtures\DaftNestedIntObject([
            'id' => 1,
            'intNestedLeft' => 0,
            'intNestedRight' => 1,
            'intNestedLevel' => 0,
            'intNestedParentId' => 0,
            'intNestedSortOrder' => 0,
        ]);

        $repo->RememberDaftObject($leaf);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not retrieve leaf from tree!');

        $repo->RecallDaftNestedObjectFullTree();
    }

    public function DataProviderInsertArgs() : Generator
    {
        foreach ([true, false] as $before) {
            foreach ([null, true, false] as $above) {
                yield [$before, $above];
            }
        }
    }

    /**
    * @dataProvider DataProviderInsertArgs
    */
    public function testModifyDaftNestedObjectTreeInsertFailsRelativeToSelf(
        bool $before,
        ? bool $above
    ) : void {
        /**
        * @var Fixtures\DaftWriteableNestedObjectIntTree $repo
        */
        $repo = Fixtures\DaftWriteableNestedObjectIntTree::DaftObjectRepositoryByType(
            Fixtures\DaftNestedWriteableIntObject::class
        );

        $leaf = new Fixtures\DaftNestedWriteableIntObject([
            'id' => 1,
            'intNestedLeft' => 0,
            'intNestedRight' => 1,
            'intNestedLevel' => 0,
            'intNestedParentId' => 0,
            'intNestedSortOrder' => 0,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot modify leaf relative to itself!');

        $repo->ModifyDaftNestedObjectTreeInsert($leaf, $leaf, $before, $above);
    }

    /**
    * @dataProvider DataProviderInsertArgs
    */
    public function testModifyDaftNestedObjectTreeInsertFailsToRetrieveLeaf(
        bool $before,
        ? bool $above
    ) : void {
        /**
        * @var Fixtures\ThrowingWriteableMemoryTree $repo
        */
        $repo = Fixtures\ThrowingWriteableMemoryTree::DaftObjectRepositoryByType(
            Fixtures\DaftNestedWriteableIntObject::class
        );

        $a0 = new Fixtures\DaftNestedWriteableIntObject([
            'id' => 1,
            'intNestedLeft' => 0,
            'intNestedRight' => 1,
            'intNestedLevel' => 0,
            'intNestedParentId' => 0,
            'intNestedSortOrder' => 0,
        ]);

        $b0 = new Fixtures\DaftNestedWriteableIntObject([
            'id' => 2,
            'intNestedLeft' => 0,
            'intNestedRight' => 1,
            'intNestedLevel' => 0,
            'intNestedParentId' => 0,
            'intNestedSortOrder' => 0,
        ]);

        $repo->ToggleRecallDaftObjectAlwaysNull(false);

        $repo->RememberDaftObject($a0);
        $repo->RememberDaftObject($b0);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not retrieve leaf from tree after rebuilding!');

        $repo->ModifyDaftNestedObjectTreeInsert($a0, $b0, $before, $above);
    }

    /**
    * @dataProvider DataProviderInsertArgs
    */
    public function testModifyDaftNestedObjectTreeInsertLooseDoesNotAllowRootAsArgOne(
        bool $before,
        ? bool $above
    ) : void {
        /**
        * @var Fixtures\ThrowingWriteableMemoryTree $repo
        */
        $repo = Fixtures\ThrowingWriteableMemoryTree::DaftObjectRepositoryByType(
            Fixtures\DaftNestedWriteableIntObject::class
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot pass root id as new leaf');

        $repo->ModifyDaftNestedObjectTreeInsertLoose(
            $repo->GetNestedObjectTreeRootId(),
            $repo->GetNestedObjectTreeRootId(),
            $before,
            $above
        );
    }

    /**
    * @dataProvider DataProviderInsertArgs
    */
    public function testModifyDaftNestedObjectTreeInsertLooseFailsToRecallReferenceNode(
        bool $before,
        ? bool $above
    ) : void {
        /**
        * @var Fixtures\ThrowingWriteableMemoryTree $repo
        */
        $repo = Fixtures\ThrowingWriteableMemoryTree::DaftObjectRepositoryByType(
            Fixtures\DaftNestedWriteableIntObject::class
        );

        $a0 = new Fixtures\DaftNestedWriteableIntObject([
            'id' => 1,
            'intNestedLeft' => 0,
            'intNestedRight' => 1,
            'intNestedLevel' => 0,
            'intNestedParentId' => 0,
            'intNestedSortOrder' => 0,
        ]);

        $b0 = new Fixtures\DaftNestedWriteableIntObject([
            'id' => 2,
            'intNestedLeft' => 0,
            'intNestedRight' => 1,
            'intNestedLevel' => 0,
            'intNestedParentId' => 0,
            'intNestedSortOrder' => 0,
        ]);

        $repo->ToggleRecallDaftObjectAlwaysNull(false);

        $repo->RememberDaftObject($a0);
        $repo->RememberDaftObject($b0);

        $repo->ToggleRecallDaftObjectAlwaysNull(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Argument 1 passed to %s::%s() did not resolve to a leaf node!',
            DaftWriteableObjectMemoryTree::class,
            'ModifyDaftNestedObjectTreeInsertLoose'
        ));

        $repo->ModifyDaftNestedObjectTreeInsertLoose(
            $a0->GetId(),
            $b0,
            $before,
            $above
        );
    }

    /**
    * @dataProvider DataProviderInsertArgs
    */
    public function testModifyDaftNestedObjectTreeInsertLooseFailsWithNonRootReferenceLeaf(
        bool $before,
        ? bool $above
    ) : void {
        /**
        * @var Fixtures\ThrowingWriteableMemoryTree $repo
        */
        $repo = Fixtures\ThrowingWriteableMemoryTree::DaftObjectRepositoryByType(
            Fixtures\DaftNestedWriteableIntObject::class
        );

        $a0 = new Fixtures\DaftNestedWriteableIntObject([
            'id' => 1,
            'intNestedLeft' => 0,
            'intNestedRight' => 1,
            'intNestedLevel' => 0,
            'intNestedParentId' => 0,
            'intNestedSortOrder' => 0,
        ]);

        $b0 = new Fixtures\DaftNestedWriteableIntObject([
            'id' => 2,
            'intNestedLeft' => 0,
            'intNestedRight' => 1,
            'intNestedLevel' => 0,
            'intNestedParentId' => 0,
            'intNestedSortOrder' => 0,
        ]);

        $repo->ToggleRecallDaftObjectAlwaysNull(false);

        $repo->RememberDaftObject($a0);
        $repo->RememberDaftObject($b0);

        $repo->ToggleRecallDaftObjectAlwaysNull(false);

        $repo->ToggleRecallDaftObjectAfterCalls(true, 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Argument 2 passed to %s::%s() did not resolve to a leaf node!',
            DaftWriteableObjectMemoryTree::class,
            'ModifyDaftNestedObjectTreeInsertLoose'
        ));

        $repo->ModifyDaftNestedObjectTreeInsertLoose(
            $a0->GetId(),
            $b0->GetId(),
            $before,
            $above
        );
    }
}
