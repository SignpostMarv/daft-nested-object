<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\SuitableForRepositoryType;
use SignpostMarv\DaftObject\TraitRememberDaftObject;

abstract class AbstractTraitRememberDaftObject
{
    public function RecallDaftNestedObjectFullTree(int $relativeDepthLimit = null) : array
    {
        return [];
    }

    public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int
    {
        return 0;
    }

    public function RememberDaftObject(SuitableForRepositoryType $object) : void
    {
    }
}
