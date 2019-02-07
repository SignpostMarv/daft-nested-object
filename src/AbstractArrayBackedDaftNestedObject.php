<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

abstract class AbstractArrayBackedDaftNestedObject extends AbstractArrayBackedDaftObject implements DaftNestedObject
{
    const COUNT_EXPECT_NON_EMPTY = 0;

    const SORTABLE_PROPERTIES = [
        'intNestedSortOrder',
    ];

    public function GetIntNestedLeft() : int
    {
        return NestedTypeParanoia::ForceInt(
            $this->RetrievePropertyValueFromData('intNestedLeft') ?? null
        );
    }

    public function GetIntNestedRight() : int
    {
        return NestedTypeParanoia::ForceInt(
            $this->RetrievePropertyValueFromData('intNestedRight') ?? null
        );
    }

    public function GetIntNestedLevel() : int
    {
        return NestedTypeParanoia::ForceInt(
            $this->RetrievePropertyValueFromData('intNestedLevel') ?? null
        );
    }

    public function GetIntNestedSortOrder() : int
    {
        return NestedTypeParanoia::ForceInt(
            $this->RetrievePropertyValueFromData('intNestedSortOrder') ?? null
        );
    }

    public function ObtainDaftNestedObjectParentId() : array
    {
        /**
        * @var string[]
        */
        $idProps = static::DaftNestedObjectParentIdProperties();

        return array_map(
            /**
            * @return scalar
            */
            function (string $prop) {
                /**
                * @var scalar
                */
                $out = $this->__get($prop);

                return $out;
            },
            $idProps
        );
    }

    public function SetIntNestedLeft(int $value) : void
    {
        if ( ! is_a(static::class, DaftNestedWriteableObject::class, true)) {
            throw new ClassDoesNotImplementClassException(
                static::class,
                DaftNestedWriteableObject::class
            );
        }

        $this->NudgePropertyValue('intNestedLeft', $value);
    }

    public function SetIntNestedRight(int $value) : void
    {
        if ( ! is_a(static::class, DaftNestedWriteableObject::class, true)) {
            throw new ClassDoesNotImplementClassException(
                static::class,
                DaftNestedWriteableObject::class
            );
        }

        $this->NudgePropertyValue('intNestedRight', $value);
    }

    public function SetIntNestedLevel(int $value) : void
    {
        if ( ! is_a(static::class, DaftNestedWriteableObject::class, true)) {
            throw new ClassDoesNotImplementClassException(
                static::class,
                DaftNestedWriteableObject::class
            );
        }

        $this->NudgePropertyValue('intNestedLevel', $value);
    }

    public function SetIntNestedSortOrder(int $value) : void
    {
        if ( ! is_a(static::class, DaftNestedWriteableObject::class, true)) {
            throw new ClassDoesNotImplementClassException(
                static::class,
                DaftNestedWriteableObject::class
            );
        }

        $this->NudgePropertyValue('intNestedSortOrder', $value);
    }

    /**
    * @param mixed $value
    */
    public function AlterDaftNestedObjectParentId($value) : void
    {
        if ( ! is_a(static::class, DaftNestedWriteableObject::class, true)) {
            throw new ClassDoesNotImplementClassException(
                static::class,
                DaftNestedWriteableObject::class
            );
        }

        /**
        * @var string[]
        */
        $props = static::DaftNestedObjectParentIdProperties();

        /**
        * @var array<string, scalar|array|object|null>
        */
        $propsAndVals = array_combine($props, (is_array($value) ? $value : [$value]));

        foreach ($propsAndVals as $prop => $val) {
            $this->NudgePropertyValue($prop, $val);
        }
    }

    public function ChangedProperties() : array
    {
        $out = parent::ChangedProperties();

        $props = array_filter(
            static::DaftNestedObjectParentIdProperties(),
            function (string $prop) use ($out) : bool {
                return in_array($prop, $out, true);
            }
        );

        if (count($props) > self::COUNT_EXPECT_NON_EMPTY) {
            $out[] = 'daftNestedObjectParentId';
        }

        return $out;
    }
}
