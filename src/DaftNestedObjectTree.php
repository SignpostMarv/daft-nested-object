<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

interface DaftNestedObjectTree extends DaftObjectRepository
{
    /**
    * @return array<int, DaftNestedObject>
    */
    public function RecallDaftNestedObjectFullTree(int $relativeDepthLimit = null) : array;

    public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int;

    /**
    * @return array<int, DaftNestedObject>
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
    * @param mixed $id
    *
    * @return array<int, DaftNestedObject>
    */
    public function RecallDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array;

    /**
    * @param mixed $id
    */
    public function CountDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : int;

    /**
    * @return array<int, DaftNestedObject>
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
    * @param mixed $id
    *
    * @return array<int, DaftNestedObject>
    */
    public function RecallDaftNestedObjectPathToId($id, bool $includeLeaf) : array;

    /**
    * @param mixed $id
    */
    public function CountDaftNestedObjectPathToId($id, bool $includeLeaf) : int;

    /**
    * @return mixed
    */
    public function GetNestedObjectTreeRootId();
}
