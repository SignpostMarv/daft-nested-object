<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

abstract class DaftWriteableObjectMemoryTree extends DaftObjectMemoryTree implements DaftNestedWriteableObjectTree
{
    use TraitRememberDaftObject;
    use TraitWriteableTree;

    public function RememberDaftObjectData(
        DefinesOwnIdPropertiesInterface $object,
        bool $assumeDoesNotExist = DaftObjectMemoryTree::BOOL_DEFAULT_ASSUME_DOES_NOT_EXIST
    ) {
        static::ThrowIfNotType(
            $object,
            DaftNestedWriteableObject::class,
            self::INT_ARG_INDEX_FIRST,
            __METHOD__
        );

        parent::RememberDaftObjectData($object, $assumeDoesNotExist);
    }

    /**
    * @param DaftObject|string $object
    */
    protected static function ThrowIfNotType(
        $object,
        string $type,
        int $argument,
        string $function
    ) {
        if ( ! is_a($object, DaftNestedWriteableObject::class, is_string($object))) {
            throw new DaftObjectRepositoryTypeByClassMethodAndTypeException(
                $argument,
                static::class,
                $function,
                DaftNestedWriteableObject::class,
                is_string($object) ? $object : get_class($object)
            );
        }

        parent::ThrowIfNotType($object, $type, $argument, $function);
    }
}
