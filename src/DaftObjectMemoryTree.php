<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use InvalidArgumentException;

/**
* @template T as DaftNestedObject
*
* @template-extends DaftObjectMemoryRepository<T>
*/
abstract class DaftObjectMemoryTree extends DaftObjectMemoryRepository implements DaftNestedObjectTree
{
    const DECREMENT = -1;

    const INCREMENT = 1;

    const BOOL_DEFAULT_ASSUME_DOES_NOT_EXIST = false;

    const INT_ARG_INDEX_FIRST = 1;

    const BOOL_DEFAULT_NO_MODIFY = 0;

    public function RecallDaftNestedObjectFullTree(int $relativeDepthLimit = null) : array
    {
        /**
        * @var array<int, DaftNestedObject>
        */
        $out = $this->memory;

        $outIds = [];

        foreach ($out as $obj) {
            /**
            * @var array<int, scalar|(scalar|array|object|null)[]>
            */
            $id = $obj->GetId();

            $outIds[] = $id;
        }

        /**
        * @var array<int, DaftNestedObject>
        */
        $fromMemory = array_filter(
            array_map([$this, 'MapDataToObject'], $this->data),
            function (DaftNestedObject $leaf) use ($outIds) : bool {
                return ! in_array($leaf->GetId(), $outIds, true);
            }
        );

        $out = array_merge($out, $fromMemory);

        usort($out, function (DaftNestedObject $a, DaftNestedObject $b) : int {
            return $a->GetIntNestedLeft() <=> $b->GetIntNestedLeft();
        });

        if (is_int($relativeDepthLimit)) {
            $filter = function (DaftNestedObject $e) use ($relativeDepthLimit) : bool {
                return $e->GetIntNestedLevel() <= $relativeDepthLimit;
            };
            $out = array_filter($out, $filter);
        }

        return $out;
    }

    public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int
    {
        return count($this->RecallDaftNestedObjectFullTree($relativeDepthLimit));
    }

    /**
    * {@inheritdoc}
    */
    public function RecallDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        ? int $limit
    ) : array {
        $left = $root->GetIntNestedLeft();
        $right = $root->GetIntNestedRight();
        $limit = is_int($limit) ? ($root->GetIntNestedLevel() + $limit) : null;

        $leaves = $this->RecallDaftNestedObjectFullTree();

        if (is_int($limit)) {
            $leaves = array_filter($leaves, function (DaftNestedObject $e) use ($limit) : bool {
                return $e->GetIntNestedLevel() <= $limit;
            });
        }

        return array_values(array_filter(
            $leaves,
            function (DaftNestedObject $e) use ($includeRoot, $left, $right) : bool {
                return $this->FilterLeaf($includeRoot, $left, $right, $e);
            }
        ));
    }

    public function CountDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : int {
        return count(
            $this->RecallDaftNestedObjectTreeWithObject($root, $includeRoot, $relativeDepthLimit)
        );
    }

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @return array<int, DaftNestedObject>
    */
    public function RecallDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array {
        $object = $this->RecallDaftObject($id);

        /**
        * @var array<int, DaftNestedObject>
        */
        $out =
            ($object instanceof DaftNestedObject)
                ? $this->RecallDaftNestedObjectTreeWithObject(
                    $object,
                    $includeRoot,
                    $relativeDepthLimit
                )
                : (
                    ((array) $id === (array) $this->GetNestedObjectTreeRootId())
                        ? $this->RecallDaftNestedObjectFullTree($relativeDepthLimit)
                        : []
                );

        return $out;
    }

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function CountDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : int {
        return count($this->RecallDaftNestedObjectTreeWithId(
            $id,
            $includeRoot,
            $relativeDepthLimit
        ));
    }

    /**
    * @return array<int, DaftNestedObject>
    */
    public function RecallDaftNestedObjectPathToObject(
        DaftNestedObject $leaf,
        bool $includeLeaf
    ) : array {
        $left =
            $leaf->GetIntNestedLeft() +
            ($includeLeaf ? self::BOOL_DEFAULT_NO_MODIFY : self::DECREMENT);
        $right =
            $leaf->GetIntNestedRight() +
            ($includeLeaf ? self::BOOL_DEFAULT_NO_MODIFY : self::INCREMENT);

        /**
        * @var array<int, DaftNestedObject>
        */
        $out = array_values(array_filter(
            $this->RecallDaftNestedObjectFullTree(),
            function (DaftNestedObject $e) use ($left, $right) : bool {
                return $e->GetIntNestedLeft() <= $left && $e->GetIntNestedRight() >= $right;
            }
        ));

        return $out;
    }

    public function CountDaftNestedObjectPathToObject(
        DaftNestedObject $leaf,
        bool $includeLeaf
    ) : int {
        return count($this->RecallDaftNestedObjectPathToObject($leaf, $includeLeaf));
    }

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @return array<int, DaftNestedObject>
    */
    public function RecallDaftNestedObjectPathToId($id, bool $includeLeaf) : array
    {
        $object = $this->RecallDaftObject($id);

        return
            ($object instanceof DaftNestedObject)
                ? $this->RecallDaftNestedObjectPathToObject($object, $includeLeaf)
                : [];
    }

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function CountDaftNestedObjectPathToId($id, bool $includeLeaf) : int
    {
        return count($this->RecallDaftNestedObjectPathToId($id, $includeLeaf));
    }

    public function RememberDaftObject(SuitableForRepositoryType $object) : void
    {
        if ( ! ($object instanceof DaftNestedObject)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                '() must be an instance of ' .
                DaftNestedObject::class .
                ', ' .
                get_class($object) .
                ' given!'
            );
        }

        if ($object instanceof DaftNestedWriteableObject) {
            $this->RememberDaftNestedWriteableObject($object);
        } else {
            parent::RememberDaftObject($object);
        }
    }

    private function RememberDaftNestedWriteableObject(DaftNestedWriteableObject $object) : void
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

    private function MapDataToObject(array $row) : DaftNestedObject
    {
        $type = $this->type;
        /**
        * @var DaftNestedObject
        */
        $out = new $type($row);

        return $out;
    }

    private function FilterLeaf(
        bool $includeRoot,
        int $left,
        int $right,
        DaftNestedObject $e
    ) : bool {
        if ($includeRoot) {
            return $e->GetIntNestedLeft() >= $left && $e->GetIntNestedRight() <= $right;
        }

        return $e->GetIntNestedLeft() > $left && $e->GetIntNestedRight() < $right;
    }
}
