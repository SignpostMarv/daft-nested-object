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
    public function SetIntNestedLeft(int $value);

    public function SetIntNestedRight(int $value);

    public function SetIntNestedLevel(int $value);

    public function SetIntNestedSortOrder(int $value);

    /**
    * @param mixed $id
    */
    public function AlterDaftNestedObjectParentId($id);
}
