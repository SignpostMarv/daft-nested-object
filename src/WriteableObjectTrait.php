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
    * @param mixed $value
    */
    public function AlterDaftNestedObjectParentId($value) : void
    {
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

    /**
    * @return array<int, string>
    */
    abstract public static function DaftNestedObjectParentIdProperties() : array;
}
