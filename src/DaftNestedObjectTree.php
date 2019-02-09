<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DaftNestedObject
*
* @template-extends DaftObjectRepository<T>
*/
interface DaftNestedObjectTree extends DaftObjectRepository
{
    /**
    * @return array<int, T>
    */
    public function RecallDaftNestedObjectFullTree(int $relativeDepthLimit = null) : array;

    public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int;

    /**
    * @return array<int, T>
    */
    public function RecallDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array;

    public function CountDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : int;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @return array<int, T>
    */
    public function RecallDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function CountDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : int;

    /**
    * @return array<int, T>
    */
    public function RecallDaftNestedObjectPathToObject(
        DaftNestedObject $leaf,
        bool $includeLeaf
    ) : array;

    public function CountDaftNestedObjectPathToObject(
        DaftNestedObject $leaf,
        bool $includeLeaf
    ) : int;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @return array<int, T>
    */
    public function RecallDaftNestedObjectPathToId($id, bool $includeLeaf) : array;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function CountDaftNestedObjectPathToId($id, bool $includeLeaf) : int;

    /**
    * @return scalar|(scalar|array|object|null)[]
    */
    public function GetNestedObjectTreeRootId();
}
