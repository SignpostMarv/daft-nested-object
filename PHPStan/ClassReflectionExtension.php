<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\PHPStan;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use RuntimeException;
use SignpostMarv\DaftObject\PHPStan\ClassReflectionExtension as Base;

/**
* @template T as \SignpostMarv\DaftObject\DaftNestedObject&\SignpostMarv\DaftObject\DaftObjectCreatedByArray
*
* @template-extends Base<T>
*/
class ClassReflectionExtension extends Base
{
    protected function ObtainPropertyReflection(
        ClassReflection $ref,
        Broker $broker,
        string $propertyName
    ) : PropertyReflection {
        return new PropertyReflectionExtension($ref, $broker, $propertyName);
    }
}
