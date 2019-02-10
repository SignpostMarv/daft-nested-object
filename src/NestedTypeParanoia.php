<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class NestedTypeParanoia
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
    * @param mixed $maybe
    */
    public static function ForceInt($maybe) : int
    {
        return is_int($maybe) ? $maybe : (int) $maybe;
    }

    public static function NotYetAppendedToTree(DaftNestedObject $object) : bool
    {
        $left = $object->GetIntNestedLeft();
        $right = $object->GetIntNestedRight();
        $level = $object->GetIntNestedLevel();

        return 0 === $left && 0 === $right && 0 === $level;
    }

    public static function ObtainFirstOrLast(
        bool $before,
        DaftNestedWriteableObject $first,
        DaftNestedWriteableObject ...$leaves
    ) : DaftNestedWriteableObject {
        array_unshift($leaves, $first);

        $reference = $before ? current($leaves) : end($leaves);

        return $reference;
    }
}
