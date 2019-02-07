<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\SuitableForRepositoryType;
use SignpostMarv\DaftObject\TraitWriteableTree;

class WriteableTraitNotTree
{
    use TraitWriteableTree;

    public function WillFail() : void
    {
        $this->ThrowIfNotTree();
    }

    /**
    * @param mixed $id
    */
    public function RecallDaftObject($id) : ? DaftObject
    {
        return null;
    }

    public function CountDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : int {
        return 0;
    }

    public function RemoveDaftObject(SuitableForRepositoryType $object) : void
    {
    }

    /**
    * @param mixed $id
    */
    public function RemoveDaftObjectById($id) : void
    {
    }

    public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int
    {
        return 0;
    }

    public function RememberDaftObject(SuitableForRepositoryType $object) : void
    {
    }

    public function ForgetDaftObject(SuitableForRepositoryType $object) : void
    {
    }

    /**
    * @param mixed $id
    */
    public function ForgetDaftObjectById($id) : void
    {
    }

    public function RecallDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array {
        return [];
    }

    public function RecallDaftNestedObjectFullTree(int $relativeDepthLimit = null) : array
    {
        return [];
    }

    /**
    * @param mixed $id
    */
    public function RecallDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array {
        return [];
    }

    protected function RememberDaftObjectData(SuitableForRepositoryType $object) : void
    {
    }
}
