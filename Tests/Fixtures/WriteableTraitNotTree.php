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
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\TraitWriteableTree;

class WriteableTraitNotTree
{
    use TraitWriteableTree;

    public function WillFail()
    {
        $this->ThrowIfNotTree();
    }

    /**
    * @param mixed $id
    *
    * @return DaftObject|null
    */
    public function RecallDaftObject($id)
    {
    }

    public function CountDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        int $relativeDepthLimit = null
    ) : int {
        return 0;
    }

    public function RemoveDaftObject(DefinesOwnIdPropertiesInterface $object)
    {
    }

    /**
    * @param mixed $id
    */
    public function RemoveDaftObjectById($id)
    {
    }

    public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int
    {
        return 0;
    }

    public function RememberDaftObject(DefinesOwnIdPropertiesInterface $object)
    {
    }

    public function ForgetDaftObject(DefinesOwnIdPropertiesInterface $object)
    {
    }

    /**
    * @param mixed $id
    */
    public function ForgetDaftObjectById($id)
    {
    }

    public function RecallDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        int $relativeDepthLimit = null
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
        int $relativeDepthLimit = null
    ) : array {
        return [];
    }

    protected function RememberDaftObjectData(DefinesOwnIdPropertiesInterface $object)
    {
    }

    /**
    * @param DaftObject|string $object
    */
    protected static function ThrowIfNotType(
        $object,
        string $type,
        int $argument,
        string $function
    ) {
    }
}
