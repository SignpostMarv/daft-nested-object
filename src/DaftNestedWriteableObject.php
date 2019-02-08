<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

interface DaftNestedWriteableObject extends DaftNestedObject
{
    public function SetIntNestedLeft(int $value) : void;

    public function SetIntNestedRight(int $value) : void;

    public function SetIntNestedLevel(int $value) : void;

    public function SetIntNestedSortOrder(int $value) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function AlterDaftNestedObjectParentId($id) : void;
}
