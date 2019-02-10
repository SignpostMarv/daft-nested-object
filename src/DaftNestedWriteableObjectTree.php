<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DaftNestedWriteableObject
*
* @template-extends DaftNestedObjectTree<T>
*/
interface DaftNestedWriteableObjectTree extends DaftNestedObjectTree
{
    /**
    * @psalm-param T $newLeaf
    * @psalm-param T $referenceLeaf
    *
    * @return DaftNestedWriteableObject a new instance, modified version of $newLeaf
    *
    * @psalm-return T
    */
    public function ModifyDaftNestedObjectTreeInsert(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before = true,
        bool $above = null
    ) : DaftNestedWriteableObject;

    /**
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $newLeaf can be an object or an id, MUST NOT be a root id
    * @param DaftNestedWriteableObject|scalar|(scalar|array|object|null)[] $referenceLeaf can be an object, an id, or a root id
    *
    * @psalm-param T|scalar|(scalar|array|object|null)[] $newLeaf
    * @psalm-param T|scalar|(scalar|array|object|null)[] $referenceLeaf
    *
    * @return DaftNestedWriteableObject a new instance, modified version of $newLeaf
    *
    * @psalm-return T
    */
    public function ModifyDaftNestedObjectTreeInsertLoose(
        $newLeaf,
        $referenceLeaf,
        bool $before = true,
        bool $above = null
    ) : DaftNestedWriteableObject;

    /**
    * @psalm-param T $root
    * @psalm-param T|null $replacementRoot
    *
    * @throws \BadMethodCallException if $root has leaves without $replacementRoot specified
    *
    * @return int full tree count after removal
    */
    public function ModifyDaftNestedObjectTreeRemoveWithObject(
        DaftNestedWriteableObject $root,
        ? DaftNestedWriteableObject $replacementRoot
    ) : int;

    /**
    * @param scalar|(scalar|array|object|null)[] $root
    * @param scalar|(scalar|array|object|null)[]|null $replacementRoot
    *
    * @throws \BadMethodCallException if $root has leaves without $replacementRoot specified
    *
    * @return int full tree count after removal
    */
    public function ModifyDaftNestedObjectTreeRemoveWithId($root, $replacementRoot) : int;

    /**
    * @psalm-param T $leaf
    *
    * @psalm-return T
    */
    public function StoreThenRetrieveFreshLeaf(
        DaftNestedWriteableObject $leaf
    ) : DaftNestedWriteableObject;
}
