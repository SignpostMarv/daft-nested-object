<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DaftNestedWriteableObject
*/
trait WriteableObjectTrait
{
    public function SetIntNestedLeft(int $value) : void
    {
        $this->NudgePropertyValue('intNestedLeft', $value);
    }

    public function SetIntNestedRight(int $value) : void
    {
        $this->NudgePropertyValue('intNestedRight', $value);
    }

    public function SetIntNestedLevel(int $value) : void
    {
        $this->NudgePropertyValue('intNestedLevel', $value);
    }

    public function SetIntNestedSortOrder(int $value) : void
    {
        $this->NudgePropertyValue('intNestedSortOrder', $value);
    }

    /**
    * @param scalar[] $value
    */
    public function SetDaftNestedObjectParentId(array $value) : void
    {
        /**
        * @var string[]
        */
        $props = static::DaftNestedObjectParentIdProperties();

        foreach (static::DaftNestedObjectParentIdProperties() as $i => $prop) {
            $this->NudgePropertyValue($prop, $value[$i] ?? null);
        }
    }

    /**
    * @return array<int, string>
    */
    abstract public static function DaftNestedObjectParentIdProperties() : array;

    /**
    * @see AbstractDaftObject::NudgePropertyValue()
    *
    * @param string $property property being nudged
    * @param scalar|array|object|null $value value to nudge property with
    */
    abstract protected function NudgePropertyValue(
        string $property,
        $value,
        bool $autoTrimStrings = AbstractArrayBackedDaftObject::BOOL_DEFAULT_AUTOTRIMSTRINGS,
        bool $throwIfNotUnique = AbstractArrayBackedDaftObject::BOOL_DEFAULT_THROWIFNOTUNIQUE
    ) : void;
}
