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
}
