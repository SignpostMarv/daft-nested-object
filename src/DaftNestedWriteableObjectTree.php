<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

interface DaftNestedWriteableObjectTree extends DaftNestedObjectTree
{
    /**
    * @return DaftNestedWriteableObject a new instance, modified version of $newLeaf
    */
    public function ModifyDaftNestedObjectTreeInsert(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before = true,
        bool $above = null
    ) : DaftNestedWriteableObject;

    /**
    * @param mixed $newLeaf can be an object or an id, MUST NOT be a root id
    * @param mixed $referenceLeaf can be an object, an id, or a root id
    *
    * @return DaftNestedWriteableObject a new instance, modified version of $newLeaf
    */
    public function ModifyDaftNestedObjectTreeInsertLoose(
        $newLeaf,
        $referenceLeaf,
        bool $before = true,
        bool $above = null
    ) : DaftNestedWriteableObject;

    /**
    * @throws \BadMethodCallException if $root has leaves without $replacementRoot specified
    *
    * @return int full tree count after removal
    */
    public function ModifyDaftNestedObjectTreeRemoveWithObject(
        DaftNestedWriteableObject $root,
        ? DaftNestedWriteableObject $replacementRoot
    ) : int;

    /**
    * @param mixed $root
    * @param scalar|scalar[]|null $replacementRoot
    *
    * @throws \BadMethodCallException if $root has leaves without $replacementRoot specified
    *
    * @return int full tree count after removal
    */
    public function ModifyDaftNestedObjectTreeRemoveWithId($root, $replacementRoot) : int;
}
