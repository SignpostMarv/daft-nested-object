<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use BadMethodCallException;
use InvalidArgumentException;
use RuntimeException;

abstract class DaftWriteableObjectMemoryTree extends DaftObjectMemoryTree implements DaftNestedWriteableObjectTree
{
    public function ModifyDaftNestedObjectTreeInsertBefore(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject {
        return $this->ModifyDaftNestedObjectTreeInsert($newLeaf, $referenceLeaf, true);
    }

    public function ModifyDaftNestedObjectTreeInsertAfter(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject {
        return $this->ModifyDaftNestedObjectTreeInsert($newLeaf, $referenceLeaf, false);
    }

    public function ModifyDaftNestedObjectTreeInsertBelow(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject {
        /**
        * @var DaftNestedObject $alter
        */
        foreach ($this->RecallDaftNestedObjectFullTree() as $alter) {
            if ( ! ($alter instanceof DaftNestedWriteableObject)) {
                throw new RuntimeException('Tree contains non-writeable objects!');
            }
            if ($alter->GetDaftNestedObjectParentId() === $referenceLeaf->GetId()) {
                $alter->SetDaftNestedObjectParentId($newLeaf->GetId());

                $this->RememberDaftObject($alter);
            }
        }

        return $this->ModifyDaftNestedObjectTreeInsertAfter($newLeaf, $referenceLeaf);
    }

    public function ModifyDaftNestedObjectTreeInsertAbove(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf
    ) : DaftNestedWriteableObject {
        $referenceLeaf->SetDaftNestedObjectParentId($newLeaf->GetId());
        $this->RememberDaftObject($referenceLeaf);

        $referenceParent = $this->RecallDaftObject($referenceLeaf->GetDaftNestedObjectParentId());

        if ($referenceParent instanceof DaftNestedWriteableObject) {
            return $this->ModifyDaftNestedObjectTreeInsertAfter($newLeaf, $referenceParent);
        }

        return $this->ModifyDaftNestedObjectTreeInsertBefore($newLeaf, $referenceLeaf);
    }

    public function ModifyDaftNestedObjectTreeRemoveWithObject(
        DaftNestedWriteableObject $root,
        ? DaftNestedWriteableObject $replacementRoot
    ) : int {
        if ($this->CountDaftNestedObjectTreeWithObject($root, false, null) > 0 && is_null($replacementRoot)) {
            throw new BadMethodCallException('Cannot leave orphan objects in a tree');
        }

        $right = $root->GetIntNestedRight();
        $width = ($right - $root->GetIntNestedLeft()) + 1;

        $this->ModifyDaftNestedObjectTreeForRemoval($right, $width);

        if ( ! is_null($replacementRoot)) {
            /**
            * @var DaftNestedObject $alter
            */
            foreach ($this->RecallDaftNestedObjectTreeWithObject($root, false, 1) as $alter) {
                if ( ! ($alter instanceof DaftNestedWriteableObject)) {
                    throw new RuntimeException(
                        'Tree for specified root contains non-writeable objects!'
                    );
                }
                $this->ModifyDaftNestedObjectTreeInsertBelow($alter, $replacementRoot);
            }
        }

        $this->RemoveDaftObject($root);

        return $this->CountDaftNestedObjectFullTree();
    }

    /**
    * {@inheritdoc}
    */
    public function ModifyDaftNestedObjectTreeRemoveWithId($root, $replacementRoot) : int
    {
        $rootObject = $this->RecallDaftObject($root);

        if ( ! ($rootObject instanceof DaftNestedWriteableObject)) {
            return $this->CountDaftNestedObjectFullTree();
        }

        if (
            ! is_null($replacementRoot) &&
            $replacementRoot !== $this->GetNestedObjectTreeRootId()
        ) {
            $replacementRootObject = $this->RecallDaftObject($replacementRoot);

            if ( ! ($replacementRootObject instanceof DaftNestedWriteableObject)) {
                throw new InvalidArgumentException(
                    'Could not locate replacement root, cannot leave orphan objects!'
                );
            }

            return $this->ModifyDaftNestedObjectTreeRemoveWithObject(
                $rootObject,
                $replacementRootObject
            );
        }

        if (
            $this->CountDaftNestedObjectTreeWithObject($rootObject, false, null) > 0 &&
            is_null($replacementRoot)
        ) {
            throw new BadMethodCallException('Cannot leave orphan objects in a tree');
        }

        /**
        * @var DaftNestedObject $alter
        */
        foreach ($this->RecallDaftNestedObjectTreeWithObject($rootObject, false, null) as $alter) {
            if ( ! ($alter instanceof DaftNestedWriteableObject)) {
                throw new RuntimeException(
                    'Tree for specified root contains non-writeable objects!'
                );
            }
            $alter->SetDaftNestedObjectParentId($replacementRoot);
            $this->RememberDaftObject($alter);
        }

        $right = $rootObject->GetIntNestedRight();
        $width = ($right - $rootObject->GetIntNestedLeft()) + 1;

        $this->ModifyDaftNestedObjectTreeForRemoval($right, $width);

        $this->RemoveDaftObject($rootObject);

        return $this->CountDaftNestedObjectFullTree();
    }

    protected function RememberDaftObjectData(DefinesOwnIdPropertiesInterface $object) : void
    {
        static::ThrowIfNotType($object, DaftNestedWriteableObject::class, 1, __METHOD__);

        parent::RememberDaftObjectData($object);
    }

    /**
    * @param DaftObject|string $object
    */
    protected static function ThrowIfNotType(
        $object,
        string $type,
        int $argument,
        string $function
    ) : void {
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

    protected function ModifyDaftNestedObjectTreeInsert(
        DaftNestedWriteableObject $newLeaf,
        DaftNestedWriteableObject $referenceLeaf,
        bool $before
    ) : DaftNestedWriteableObject {
        $newLeft =
            $before
                ? $referenceLeaf->GetIntNestedLeft()
                : $referenceLeaf->GetIntNestedRight();
        $newRight = $newLeft + 1;

        $newLeaf->SetDaftNestedObjectParentId($referenceLeaf->GetDaftNestedObjectParentId());
        $newLeaf->SetIntNestedLevel($referenceLeaf->GetIntNestedLevel());

        $this->ForgetDaftObjectById($newLeaf->GetId());

        /**
        * @var DaftNestedObject $alter
        */
        foreach ($this->RecallDaftNestedObjectFullTree() as $alter) {
            if ( ! ($alter instanceof DaftNestedWriteableObject)) {
                throw new RuntimeException('Tree contains non-writeable objects!');
            }
            $alterLeft = $alter->GetIntNestedLeft();
            $alterRight = $alter->GetIntNestedRight();
            $changed = false;

            if ($alterLeft > $newLeft) {
                $alter->SetIntNestedLeft($alterLeft + 2);
            }
            if ($alterRight > $newLeft) {
                $alter->SetIntNestedRight($alterRight + 2);
            }

            $this->RememberDaftObject($alter);
        }

        $newLeaf->SetIntNestedLeft($newLeft);
        $newLeaf->SetIntNestedRight($newRight);

        $this->RememberDaftObject($newLeaf);

        $out = $this->RecallDaftObject($newLeaf->GetId());

        if ( ! ($out instanceof DaftNestedWriteableObject)) {
            throw new RuntimeException('Could not retrieve fresh copy of specified leaf!');
        }

        return $out;
    }

    protected function ModifyDaftNestedObjectTreeForRemoval(int $right, int $width) : void
    {
        /**
        * @var DaftNestedObject $alter
        */
        foreach ($this->RecallDaftNestedObjectFullTree() as $alter) {
            if ( ! ($alter instanceof DaftNestedWriteableObject)) {
                throw new RuntimeException('Tree contains non-writeable objects!');
            }
            $alterLeft = $alter->GetIntNestedLeft();
            $alterRight = $alter->GetIntNestedRight();
            $changed = false;

            if ($alterRight > $right) {
                $alter->SetIntNestedRight($alterRight - $width);
                $changed = true;
            }
            if ($alterLeft > $right) {
                $alter->SetIntNestedLeft($alterLeft - $width);
                $changed = true;
            }

            if ($changed) {
                $this->RememberDaftObject($alter);
            }
        }
    }
}
