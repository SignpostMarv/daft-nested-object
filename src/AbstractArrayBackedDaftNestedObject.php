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
    const SORTABLE_PROPERTIES = [
        'intNestedSortOrder',
    ];

    public function GetIntNestedLeft() : int
    {
        return (int) ($this->RetrievePropertyValueFromData('intNestedLeft') ?? null);
    }

    public function GetIntNestedRight() : int
    {
        return (int) ($this->RetrievePropertyValueFromData('intNestedRight') ?? null);
    }

    public function GetIntNestedLevel() : int
    {
        return (int) ($this->RetrievePropertyValueFromData('intNestedLevel') ?? null);
    }

    public function GetIntNestedSortOrder() : int
    {
        return (int) ($this->RetrievePropertyValueFromData('intNestedSortOrder') ?? null);
    }

    public function ObtainDaftNestedObjectParentId() : array
    {
        /**
        * @var string[]
        */
        $idProps = static::DaftNestedObjectParentIdProperties();

        return array_map(
            /**
            * @return mixed
            */
            function (string $prop) {
                return $this->$prop;
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

        foreach (array_combine($props, (is_array($value) ? $value : [$value])) as $prop => $val) {
            $this->NudgePropertyValue($prop, $val);
        }
    }

    public function ChangedProperties() : array
    {
        $out = parent::ChangedProperties();

        foreach (static::DaftNestedObjectParentIdProperties() as $prop) {
            if (in_array($prop, $out, true)) {
                $out[] = 'daftNestedObjectParentId';

                break;
            }
        }

        return $out;
    }
}
