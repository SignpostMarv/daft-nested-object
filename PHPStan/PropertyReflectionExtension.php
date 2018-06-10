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
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\PHPStan\PropertyReflectionExtension as Base;

class PropertyReflectionExtension extends Base
{
    /**
    * @var bool
    */
    private $nestedReadable = false;

    /**
    * @var bool
    */
    private $nestedWriteable = false;

    /**
    * @var IntegerType
    */
    private $nestedType = null;

    protected static function PropertyIsInt(string $property) : bool
    {
        return in_array($property, [
            'intNestedLeft',
            'intNestedRight',
            'intNestedLevel',
            'intNestedSortOrder',
        ]);
    }

    public function __construct(ClassReflection $classReflection, Broker $broker, string $property)
    {
        parent::__construct($classReflection, $broker, $property);

        $intProperty = static::PropertyIsInt($property);

        if ('intNestedParentId' === $property || $intProperty) {
            $this->nestedReadable = true;
            $this->nestedWriteable = is_a(
                $classReflection->getNativeReflection()->getName(),
                DaftNestedWriteableObject::class,
                true
            );
        }

        if ($intProperty) {
            $this->nestedType = new IntegerType();
        }
    }

    public function isReadable() : bool
    {
        return $this->nestedReadable || parent::isReadable();
    }

    public function isWritable() : bool
    {
        return $this->nestedWriteable || parent::isWritable();
    }

    public function getType() : Type
    {
        return $this->nestedType ?? parent::getType();
    }

    protected static function PropertyIsPublic(string $className, string $property) : bool
    {
        return
            static::PropertyIsInt($property) ||
            parent::PropertyIsPublic($className, $property);
    }
}
