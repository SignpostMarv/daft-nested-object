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
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\PHPStan\PropertyReflectionExtension as Base;
use SignpostMarv\DaftObject\TypeParanoia;

class PropertyReflectionExtension extends Base
{
    const BOOL_NESTED_IS_READABLE = false;

    /**
    * @var bool
    */
    private $nestedReadable = false;

    /**
    * @var bool
    */
    private $nestedWriteable = false;

    /**
    * @var IntegerType|null
    */
    private $nestedType = null;

    public function __construct(ClassReflection $classReflection, Broker $broker, string $property)
    {
        parent::__construct($classReflection, $broker, $property);

        $intProperty = static::PropertyIsInt($property);

        if ('intNestedParentId' === $property || $intProperty) {
            $this->nestedReadable = self::BOOL_NESTED_IS_READABLE;
            $this->nestedWriteable = TypeParanoia::IsThingStrings(
                $classReflection->getNativeReflection()->getName(),
                DaftNestedWriteableObject::class
            );
        }

        if ($intProperty) {
            $this->nestedType = new IntegerType();
        }
    }

    private static function PropertyIsInt(string $property) : bool
    {
        return TypeParanoia::MaybeInArray(
            $property,
            [
                'intNestedLeft',
                'intNestedRight',
                'intNestedLevel',
                'intNestedSortOrder',
            ]
        );
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
