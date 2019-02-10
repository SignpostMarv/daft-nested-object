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
use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DaftNestedObjectTree;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObjectTree;
use SignpostMarv\DaftObject\DaftObjectRepository;
use SignpostMarv\DaftObject\DaftObjectNotRecalledException;
use SignpostMarv\DaftObject\SuitableForRepositoryType;
use SignpostMarv\DaftObject\Tests\TestCase as Base;

/**
* @template T as DaftNestedWriteableObject
*/
class CoverageTest extends Base
{
    public function DataProviderCoverageNonWriteableRepo() : Generator
    {
        /**
        * @var Fixtures\ThrowingMemoryTree
        */
        $repo = Fixtures\ThrowingMemoryTree::DaftObjectRepositoryByType(
            Fixtures\DaftNestedIntObject::class
        );

        yield [$repo];
    }

    public function DataProviderCoverageWriteableRepo() : Generator
    {
        /**
        * @var Fixtures\DaftWriteableNestedObjectIntTree
        */
        $repo = Fixtures\DaftWriteableNestedObjectIntTree::DaftObjectRepositoryByType(
            Fixtures\DaftNestedWriteableIntObject::class
        );

        yield [$repo];

        /**
        * @var Fixtures\ThrowingWriteableMemoryTree
        */
        $repo = Fixtures\ThrowingWriteableMemoryTree::DaftObjectRepositoryByType(
            Fixtures\DaftNestedWriteableIntObject::class
        );

        yield [$repo];
    }

    public function DataProviderCoverageWriteableRepoWithThrowingTree() : Generator
    {
        /**
        * @var array
        */
        foreach ($this->DataProviderCoverageWriteableRepo() as $args) {
            if ($args[0] instanceof Fixtures\DaftObjectWriteableThrowingTree) {
                yield $args;
            }
        }
    }

    public function DataProviderInsertArgs() : Generator
    {
        foreach ([true, false] as $before) {
            foreach ([null, true, false] as $above) {
                /**
                * @var array
                */
                foreach ($this->DataProviderCoverageWriteableRepo() as $repoArgs) {
                    list($repo) = $repoArgs;
                    yield [$repo, $before, $above];
                }
            }
        }
    }

    public function DataProviderInsertArgsWithThrowingTree() : Generator
    {
        /**
        * @var array
        */
        foreach ($this->DataProviderInsertArgs() as $args) {
            if ($args[0] instanceof Fixtures\DaftObjectWriteableThrowingTree) {
                yield $args;
            }
        }
    }

    /**
    * @dataProvider DataProviderInsertArgs
    */
    public function testModifyDaftNestedObjectTreeInsertFailsRelativeToSelf(
        DaftNestedWriteableObjectTree $repo,
        bool $before,
        ? bool $above
    ) : void {
        list($leaf) = static::PrepRepoWriteable(
            $repo,
            Fixtures\DaftNestedWriteableIntObject::class,
            1
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot modify leaf relative to itself!');

        $repo->ModifyDaftNestedObjectTreeInsert($leaf, $leaf, $before, $above);
    }

    /**
    * @dataProvider DataProviderCoverageWriteableRepoWithThrowingTree
    */
    public function testStoreThenRetrieveFreshLeafFails(
        Fixtures\DaftObjectWriteableThrowingTree $repo
    ) : void {
        list($leaf) = static::PrepRepoWriteable(
            $repo,
            Fixtures\DaftNestedWriteableIntObject::class,
            1
        );

        $repo->ToggleRecallDaftObjectAlwaysNull(true);

        $this->expectException(DaftObjectNotRecalledException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ' .
            DaftObjectRepository::class .
            '::RecallDaftObjectOrThrow() did not resolve to an instance of ' .
            SuitableForRepositoryType::class .
            ' from ' .
            get_class($repo) .
            '::RecallDaftObject()'
        );

        $repo->StoreThenRetrieveFreshLeafPublic($leaf);
    }

    /**
    * @dataProvider DataProviderInsertArgsWithThrowingTree
    */
    public function testModifyDaftNestedObjectTreeInsertFailsToRetrieveLeaf(
        Fixtures\DaftObjectWriteableThrowingTree $repo,
        bool $before,
        ? bool $above
    ) : void {
        $repo->ToggleRecallDaftObjectAlwaysNull(false);

        list($a0, $b0) = static::PrepRepoWriteable(
            $repo,
            Fixtures\DaftNestedWriteableIntObject::class,
            1,
            2
        );

        $this->expectException(DaftObjectNotRecalledException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ' .
            DaftObjectRepository::class .
            '::RecallDaftObjectOrThrow() did not resolve to an instance of ' .
            SuitableForRepositoryType::class .
            ' from ' .
            get_class($repo) .
            '::RecallDaftObject()'
        );

        $repo->ModifyDaftNestedObjectTreeInsert($a0, $b0, $before, $above);
    }

    /**
    * @dataProvider DataProviderInsertArgs
    */
    public function testModifyDaftNestedObjectTreeInsertLooseDoesNotAllowRootAsArgOne(
        DaftNestedWriteableObjectTree $repo,
        bool $before,
        ? bool $above
    ) : void {
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
    public function testModifyDaftNestedObjectTreeInsertAdjacentFailsWithNonSibling(
        DaftNestedWriteableObjectTree $repo,
        bool $before,
        ? bool $above
    ) : void {
        /**
        * @var Fixtures\DaftWriteableNestedObjectIntTree
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

        /**
        * @psalm-var Fixtures\DaftNestedWriteableIntObject
        */
        $a0 = $a0;

        /**
        * @psalm-var Fixtures\DaftNestedWriteableIntObject
        */
        $b0 = $b0;

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
    * @psalm-param class-string<T> $type
    *
    * @return array<int, DaftNestedWriteableObject>
    *
    * @psalm-return array<int, T>
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
        * @var array<int, DaftNestedWriteableObject>
        *
        * @psalm-var array<int, T>
        */
        $out = static::PrepRepo($repo, $type, ...$ids);

        return $out;
    }
}
