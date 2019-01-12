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

class ClassReflectionExtension extends Base
{
    /**
    * @var Broker|null
    */
    private $broker;

    public function setBroker(Broker $broker)
    {
        $this->broker = $broker;
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName) : bool
    {
        $property = ucfirst($propertyName);

        return
            parent::hasProperty($classReflection, $property) ||
            PropertyReflectionExtension::PropertyIsInt($property);
    }

    public function getProperty(ClassReflection $ref, string $propertyName) : PropertyReflection
    {
        if ( ! ($this->broker instanceof Broker)) {
            throw new RuntimeException('Broker not available!');
        }

        return new PropertyReflectionExtension($ref, $this->broker, $propertyName);
    }
}
