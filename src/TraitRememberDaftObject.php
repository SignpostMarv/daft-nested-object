<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use InvalidArgumentException;

trait TraitRememberDaftObject
{
    public function RememberDaftObject(SuitableForRepositoryType $object) : void
    {
        if ( ! ($object instanceof DaftNestedWriteableObject)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                '() must be an instance of ' .
                DaftNestedWriteableObject::class .
                ', ' .
                get_class($object) .
                ' given!'
            );
        }

            $this->RememberDaftObjectWriteableTyped($object);
    }

    /**
    * @return array<int, DaftNestedObject>
    */
    abstract public function RecallDaftNestedObjectFullTree(
        int $relativeDepthLimit = null
    ) : array;

    abstract public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int;

    private function RememberDaftObjectWriteableTyped(DaftNestedWriteableObject $object) : void
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
