<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait TraitRememberDaftObject
{
    public function RememberDaftObject(DefinesOwnIdPropertiesInterface $object)
    {
        if ($object instanceof DaftNestedWriteableObject) {
            $this->RememberDaftObjectWriteableTyped($object);
        } else {
            NestedTypeParanoia::ThrowIfNotWriteableNestedType(
                $object,
                1,
                static::class,
                __FUNCTION__
            );
        }
    }

    /**
    * @return array<int, DaftNestedObject>
    */
    abstract public function RecallDaftNestedObjectFullTree(
        int $relativeDepthLimit = null
    ) : array;

    abstract public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int;

    private function RememberDaftObjectWriteableTyped(DaftNestedWriteableObject $object)
    {
        $left = $object->GetIntNestedLeft();
        $right = $object->GetIntNestedRight();
        $level = $object->GetIntNestedLevel();

        if (0 === $left && 0 === $right && 0 === $level) {
            $fullTreeCount = $this->CountDaftNestedObjectFullTree();

            if ($fullTreeCount > AbstractArrayBackedDaftNestedObject::COUNT_EXPECT_NON_EMPTY) {
                $tree = $this->RecallDaftNestedObjectFullTree();

                /**
                * @var DaftNestedWriteableObject
                */
                $end = end($tree);

                $left = $end->GetIntNestedRight() + 1;
            } else {
                $left = $fullTreeCount + $fullTreeCount;
            }

            $object->SetIntNestedLeft($left);
            $object->SetIntNestedRight($left + 1);
        }

        parent::RememberDaftObject($object);
    }
}
