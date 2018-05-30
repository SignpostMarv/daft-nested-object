<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\PHPStan;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\PHPStan\ClassReflectionExtension as Base;

class ClassReflectionExtension extends Base
{
    /**
    * @var Broker|null
    */
    private $broker;

    public function setBroker(Broker $broker) : void
    {
        $this->broker = $broker;
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName) : bool
    {
        $className = $classReflection->getName();

        $property = ucfirst($propertyName);

        return
            parent::hasProperty($classReflection, $property) ||
            in_array($property, [
                'intNestedLeft',
                'intNestedRight',
                'intNestedLevel',
                'intNestedParentId'
            ]);
    }

    public function getProperty(ClassReflection $ref, string $propertyName) : PropertyReflection
    {
        return new PropertyReflectionExtension($ref, $this->broker, $propertyName);
    }
}
