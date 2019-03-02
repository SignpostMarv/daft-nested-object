<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait TraitDaftNestedObjectIntTree
{
    /**
    * @return int[]
    */
    public function GetNestedObjectTreeRootId() : array
    {
        return [0];
    }
}
