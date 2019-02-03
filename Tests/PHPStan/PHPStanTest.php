<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\PHPStan;

use SignpostMarv\DaftObject\Tests\PHPStan\PHPStanTest as Base;

class PHPStanTest extends Base
{
    protected static function ObtainConfiguration() : string
    {
        return  __DIR__ . '/../../phpstan.neon';
    }
}
