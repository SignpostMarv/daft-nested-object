<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class NestedTypeParanoia extends TypeParanoia
{
    /**
    * @param mixed $needle
    */
    public static function MaybeFoundInArray($needle, array $haystack) : ? int
    {
        $out = array_search($needle, $haystack, true);

        return is_int($out) ? $out : null;
    }

    /**
    * @param mixed $object
    */
    public static function ThrowIfNotNestedType(
        $object,
        int $argument,
        string $class,
        string $function,
        string ...$types
    ) : void {
        static::ThrowIfNotType(
            $object,
            $argument,
            $class,
            $function,
            DaftNestedObject::class,
            ...$types
        );
    }

    /**
    * @param mixed $object
    */
    public static function ThrowIfNotWriteableNestedType(
        $object,
        int $argument,
        string $class,
        string $function,
        string ...$types
    ) : void {
        static::ThrowIfNotType(
            $object,
            $argument,
            $class,
            $function,
            DaftNestedWriteableObject::class,
            ...$types
        );
    }

    public static function ForceInt($maybe) : int
    {
        return is_int($maybe) ? $maybe : (int) $maybe;
    }
}
