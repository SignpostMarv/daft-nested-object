<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DaftNestedObject
*
* @template-extends DaftObjectMemoryRepository<T>
*
* @template-implements DaftNestedObjectTree<T>
*/
abstract class DaftObjectMemoryTree extends DaftObjectMemoryRepository implements DaftNestedObjectTree
{
    const BOOL_DEFAULT_ASSUME_DOES_NOT_EXIST = false;

    const BOOL_DEFAULT_NO_MODIFY = 0;

    public function RecallDaftNestedObjectFullTree(int $relativeDepthLimit = null) : array
    {
        /**
        * @var array<int, DaftNestedObject>
        *
        * @psalm-var array<int, T>
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
        *
        * @psalm-var array<int, T>
        */
        $fromMemory = array_filter(
            array_map([$this, 'MapDataToObject'], $this->data),
            function (DaftNestedObject $leaf) use ($outIds) : bool {
                return ! in_array($leaf->GetId(), $outIds, true);
            }
        );

        $out = array_merge($out, $fromMemory);

        usort(
            $out,
            /**
            * @psalm-param T $a
            * @psalm-param T $b
            */
            function (DaftNestedObject $a, DaftNestedObject $b) : int {
                return $a->GetIntNestedLeft() <=> $b->GetIntNestedLeft();
            }
        );

        if (is_int($relativeDepthLimit)) {
            $out = array_filter(
                $out,
                /**
                * @psalm-param T $e
                */
                function (DaftNestedObject $e) use ($relativeDepthLimit) : bool {
                    return $e->GetIntNestedLevel() <= $relativeDepthLimit;
                }
            );
        }

        return $out;
    }

    public function CountDaftNestedObjectFullTree(int $relativeDepthLimit = null) : int
    {
        return count($this->RecallDaftNestedObjectFullTree($relativeDepthLimit));
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-param T $root
    *
    * @psalm-return array<int, T>
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
            /**
            * @psalm-param T $e
            */
            function (DaftNestedObject $e) use ($includeRoot, $left, $right) : bool {
                return $this->FilterLeaf($includeRoot, $left, $right, $e);
            }
        ));
    }

    /**
    * @psalm-param T $root
    */
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
    *
    * @psalm-return array<int, T>
    */
    public function RecallDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array {
        $object = $this->RecallDaftObject($id);

        /**
        * @var array<int, DaftNestedObject>
        *
        * @psalm-var array<int, T>
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
    * @psalm-param T $leaf
    *
    * @return array<int, DaftNestedObject>
    *
    * @psalm-return array<int, T>
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
        *
        * @psalm-var array<int, T>
        */
        $out = array_values(array_filter(
            $this->RecallDaftNestedObjectFullTree(),
            function (DaftNestedObject $e) use ($left, $right) : bool {
                return $e->GetIntNestedLeft() <= $left && $e->GetIntNestedRight() >= $right;
            }
        ));

        return $out;
    }

    /**
    * @psalm-param T $leaf
    */
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
    *
    * @psalm-return array<int, T>
    */
    public function RecallDaftNestedObjectPathToId($id, bool $includeLeaf) : array
    {
        /**
        * @var DaftNestedObject|null
        *
        * @psalm-var T|null
        */
        $object = $this->RecallDaftObject($id);

        /**
        * @var array<int, DaftNestedObject>
        *
        * @psalm-var array<int, T>
        */
        $out =
            ($object instanceof DaftNestedObject)
                ? $this->RecallDaftNestedObjectPathToObject($object, $includeLeaf)
                : [];

        return $out;
    }

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function CountDaftNestedObjectPathToId($id, bool $includeLeaf) : int
    {
        return count($this->RecallDaftNestedObjectPathToId($id, $includeLeaf));
    }

    /**
    * @psalm-param T $object
    */
    public function RememberDaftObject(SuitableForRepositoryType $object) : void
    {
        parent::RememberDaftObject($object);
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-return T|null
    */
    public function RecallDaftObject($id) : ? SuitableForRepositoryType
    {
        return parent::RecallDaftObject($id);
    }

    /**
    * @psalm-return T
    */
    private function MapDataToObject(array $row) : DaftNestedObject
    {
        $type = $this->type;

        /**
        * @var DaftNestedObject
        *
        * @psalm-var T
        */
        $out = new $type($row);

        return $out;
    }

    /**
    * @psalm-param T $e
    */
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
