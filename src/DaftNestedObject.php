<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

interface DaftNestedObject extends DefinesOwnUntypedIdInterface
{
    public function GetIntNestedLeft() : int;

    public function GetIntNestedRight() : int;

    public function GetIntNestedLevel() : int;

    /**
    * @return mixed
    */
    public function GetDaftNestedObjectParentId();
}
