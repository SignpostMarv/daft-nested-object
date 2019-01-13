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
        NestedTypeParanoia::ThrowIfNotWriteableNestedType(
            $object,
            self::INT_ARG_INDEX_FIRST,
            static::class,
            __METHOD__
        );

        parent::RememberDaftObjectData($object, $assumeDoesNotExist);
    }

    /**
    * {@inheritdoc}
    */
    public static function DaftObjectRepositoryByType(
        string $type,
        ...$args
    ) : DaftObjectRepository {
        NestedTypeParanoia::ThrowIfNotWriteableNestedType($type, 1, static::class, __FUNCTION__);

        return parent::DaftObjectRepositoryByType($type, ...$args);
    }
}
