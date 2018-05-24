<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

interface DaftNestedArrayObject extends
    DaftNestedObject,
    DefinesOwnArrayIdInterface
{
    public function GetIntNestedLevel() : int;

    public function GetIntNestedLeft() : int;

    public function GetIntNestedRight() : int;

    public function GetDaftNestedObjectParentId() : array;
}
