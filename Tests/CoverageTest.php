<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests;

use BadMethodCallException;
use Generator;
use InvalidArgumentException;
use RuntimeException;
use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DaftNestedObjectTree;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObjectTree;
use SignpostMarv\DaftObject\Tests\TestCase as Base;
use SignpostMarv\DaftObject\TraitWriteableTree;

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

        list($leaf) = static::PrepRepo($repo, Fixtures\DaftNestedIntObject::class, 1);

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

        list($leaf) = static::PrepRepoWriteable(
            $repo,
            Fixtures\DaftNestedWriteableIntObject::class,
            1
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot modify leaf relative to itself!');

        $repo->ModifyDaftNestedObjectTreeInsert($leaf, $leaf, $before, $above);
    }

    public function testStoreThenRetrieveFreshLeafFails() : void
    {
        /**
        * @var Fixtures\ThrowingWriteableMemoryTree $repo
        */
        $repo = Fixtures\ThrowingWriteableMemoryTree::DaftObjectRepositoryByType(
            Fixtures\DaftNestedWriteableIntObject::class
        );

        list($leaf) = static::PrepRepoWriteable(
            $repo,
            Fixtures\DaftNestedWriteableIntObject::class,
            1
        );

        $repo->ToggleRecallDaftObjectAlwaysNull(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Was not able to obtain a fresh copy of the object!');

        $repo->StoreThenRetrieveFreshLeafPublic($leaf);
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

        $repo->ToggleRecallDaftObjectAlwaysNull(false);

        list($a0, $b0) = static::PrepRepoWriteable(
            $repo,
            Fixtures\DaftNestedWriteableIntObject::class,
            1,
            2
        );

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

        $repo->ToggleRecallDaftObjectAlwaysNull(false);

        list($a0, $b0) = static::PrepRepoWriteable(
            $repo,
            Fixtures\DaftNestedWriteableIntObject::class,
            1,
            2
        );

        $repo->ToggleRecallDaftObjectAlwaysNull(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Argument 1 passed to %s::%s() did not resolve to a leaf node!',
            TraitWriteableTree::class,
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

        $repo->ToggleRecallDaftObjectAlwaysNull(false);

        list($a0, $b0) = static::PrepRepoWriteable(
            $repo,
            Fixtures\DaftNestedWriteableIntObject::class,
            1,
            2
        );

        $repo->ToggleRecallDaftObjectAfterCalls(true, 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Argument 2 passed to %s::%s() did not resolve to a leaf node!',
            TraitWriteableTree::class,
            'ModifyDaftNestedObjectTreeInsertLoose'
        ));

        $repo->ModifyDaftNestedObjectTreeInsertLoose(
            $a0->GetId(),
            $b0->GetId(),
            $before,
            $above
        );
    }

    /**
    * @dataProvider DataProviderInsertArgs
    */
    public function testModifyDaftNestedObjectTreeInsertAdjacentFailsWithNonSibling(
        bool $before,
        ? bool $above
    ) : void {
        /**
        * @var Fixtures\DaftWriteableNestedObjectIntTree $repo
        */
        $repo = Fixtures\DaftWriteableNestedObjectIntTree::DaftObjectRepositoryByType(
            Fixtures\DaftNestedWriteableIntObject::class
        );

        list($a0, $b0) = static::PrepRepoWriteable(
            $repo,
            Fixtures\DaftNestedWriteableIntObject::class,
            1,
            2
        );

        $repo->ModifyDaftNestedObjectTreeInsert($a0, $b0, false, true);

        $a0 = $repo->RecallDaftObject($a0->GetId());
        $b0 = $repo->RecallDaftObject($b0->GetId());

        if ( ! ($a0 instanceof Fixtures\DaftNestedWriteableIntObject)) {
            throw new RuntimeException('Could not retrieve fresh object!');
        } elseif ( ! ($b0 instanceof Fixtures\DaftNestedWriteableIntObject)) {
            throw new RuntimeException('Could not retrieve fresh object!');
        }

        $ref = new \ReflectionMethod($repo, 'ModifyDaftNestedObjectTreeInsertAdjacent');
        $ref->setAccessible(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Reference leaf not found in siblings tree!');

        $ref->invoke($repo, $a0, $b0, $before);
    }

    public function testTraitThrowsIfNotTree() : void
    {
        $obj = new Fixtures\WriteableTraitNotTree();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Cannot call ThrowIfNotTree on ' .
            Fixtures\WriteableTraitNotTree::class .
            ', class does not implement ' .
            DaftNestedWriteableObjectTree::class
        );

        $obj->WillFail();
    }

    /**
    * @return array<int, DaftNestedObject>
    */
    protected static function PrepRepo(
        DaftNestedObjectTree $repo,
        string $type,
        int ...$ids
    ) : array {
        if ( ! is_a($type, DaftNestedObject::class, true)) {
            throw new InvalidArgumentException('Cannot generate leaves from type!');
        }

        return array_map(
            function (int $id) use ($type, $repo) : DaftNestedObject {
                $leaf = new $type([
                    'id' => $id,
                    'intNestedLeft' => 0,
                    'intNestedRight' => 0,
                    'intNestedLevel' => 0,
                    'intNestedParentId' => 0,
                    'intNestedSortOrder' => 0,
                ]);

                $repo->RememberDaftObject($leaf);

                return $leaf;
            },
            $ids
        );
    }

    /**
    * @return array<int, DaftNestedWriteableObject>
    */
    protected static function PrepRepoWriteable(
        DaftNestedObjectTree $repo,
        string $type,
        int ...$ids
    ) : array {
        if ( ! is_a($type, DaftNestedWriteableObject::class, true)) {
            throw new InvalidArgumentException('Cannot generate leaves from type!');
        }

        /**
        * @var array<int, DaftNestedWriteableObject> $out
        */
        $out = static::PrepRepo($repo, $type, ...$ids);

        return $out;
    }
}
