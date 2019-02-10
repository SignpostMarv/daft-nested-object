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
use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\PHPStan\ClassReflectionExtension as Base;

/**
* @template TObj as DaftNestedObject
*
* @template-extends Base<TObj>
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

    public function hasProperty(ClassReflection $classReflection, string $propertyName) : bool
    {
        $property = ucfirst($propertyName);

        return
            parent::hasProperty($classReflection, $property) ||
            PropertyReflectionExtension::PropertyIsInt($property);
    }
}
