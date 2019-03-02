<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as AbstractArrayBackedDaftNestedObject
*
* @template-implements DaftNestedObject<T>
*
* @property-read int $intNestedLeft
* @property-read int $intNestedRight
* @property-read int $intNestedLevel
* @property-read int $intNestedSortOrder
* @property-read scalar[] $daftNestedObjectParentId
*/
abstract class AbstractArrayBackedDaftNestedObject extends AbstractArrayBackedDaftObject implements DaftNestedObject
{
    /**
    * @use TraitSortableDaftObject<T>
    */
    use TraitSortableDaftObject;

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

    /**
    * @return scalar[]
    */
    public function GetDaftNestedObjectParentId() : array
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

    public static function DaftObjectExportableProperties() : array
    {
        return array_filter(
            parent::DaftObjectExportableProperties(),
            function (string $prop) : bool {
                return 'daftNestedObjectParentId' !== $prop;
            }
        );
    }
}
