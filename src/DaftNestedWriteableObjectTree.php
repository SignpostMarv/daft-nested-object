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
    public function ModifyDaftNestedObjectTreeInsertBefore(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject;

    /**
    * @return DaftNestedWriteableObject a new instance, modified version of $newLeaf
    */
    public function ModifyDaftNestedObjectTreeInsertAfter(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject;

    /**
    * @return DaftNestedWriteableObject a new instance, modified version of $newLeaf
    */
    public function ModifyDaftNestedObjectTreeInsertBelow(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject;

    /**
    * @return DaftNestedWriteableObject a new instance, modified version of $newLeaf
    */
    public function ModifyDaftNestedObjectTreeInsertAbove(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
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
    * @param mixed $replacementRoot
    *
    * @throws \BadMethodCallException if $root has leaves without $replacementRoot specified
    *
    * @return int full tree count after removal
    */
    public function ModifyDaftNestedObjectTreeRemoveWithId($root, $replacementRoot) : int;
}
