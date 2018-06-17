<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

abstract class DaftObjectMemoryTree extends DaftObjectMemoryRepository implements DaftNestedObjectTree
{
    public function RecallDaftNestedObjectFullTree(int $relativeDepthLimit = null) : array
    {
        $type = $this->type;

        /**
        * @var DaftNestedObject[] $out
        */
        $out = $this->memory;

        /**
        * @var array<int, scalar|scalar[]> $outIds
        */
        $outIds = [];

        foreach ($out as $obj) {
            /**
            * @var array<int, scalar|scalar[]> $id
            */
            $id = $obj->GetId();

            $outIds[] = $id;
        }

        /**
        * @var DaftNestedObject[] $fromMemory
        */
        $fromMemory = array_filter(
            array_map(
                /**
                * @param array<string, scalar|null> $row
                */
                function (array $row) use ($type) : DaftNestedObject {
                    /**
                    * @var DaftNestedObject $out
                    */
                    $out = new $type($row);

                    return $out;
                },
                (array) $this->data
            ),
            function (DaftNestedObject $leaf) use ($outIds) : bool {
                return ! in_array($leaf->GetId(), $outIds, true);
            }
        );

        $out = array_merge($out, $fromMemory);

        usort($out, function (DaftNestedObject $a, DaftNestedObject $b) : int {
            return $a->GetIntNestedLeft() <=> $b->GetIntNestedLeft();
        });

        if (is_int($relativeDepthLimit)) {
            $out = array_filter(
                $out,
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

    public function RecallDaftNestedObjectTreeWithId(
        $id,
        bool $includeRoot,
        ? int $relativeDepthLimit
    ) : array {
        $object = $this->RecallDaftObject($id);

        return
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
    }

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

    public function RecallDaftNestedObjectPathToObject(
        DaftNestedObject $leaf,
        bool $includeLeaf
    ) : array {
        $left = $leaf->GetIntNestedLeft();
        $right = $leaf->GetIntNestedRight();

        if ( ! $includeLeaf) {
            --$left;
            ++$right;
        }

        return array_values(array_filter(
            $this->RecallDaftNestedObjectFullTree(),
            function (DaftNestedObject $e) use ($left, $right) : bool {
                return $e->GetIntNestedLeft() <= $left && $e->GetIntNestedRight() >= $right;
            }
        ));
    }

    public function CountDaftNestedObjectPathToObject(
        DaftNestedObject $leaf,
        bool $includeLeaf
    ) : int {
        return count($this->RecallDaftNestedObjectPathToObject($leaf, $includeLeaf));
    }

    public function RecallDaftNestedObjectPathToId($id, bool $includeLeaf) : array
    {
        $object = $this->RecallDaftObject($id);

        return
            ($object instanceof DaftNestedObject)
                ? $this->RecallDaftNestedObjectPathToObject($object, $includeLeaf)
                : [];
    }

    /*
    * @param mixed $id
    */
    public function CountDaftNestedObjectPathToId($id, bool $includeLeaf) : int
    {
        return count($this->RecallDaftNestedObjectPathToId($id, $includeLeaf));
    }

    public function CompareObjects(DaftNestedObject $a, DaftNestedObject $b) : int
    {
        return $a->GetIntNestedSortOrder() <=> $b->GetIntNestedSortOrder();
    }

    protected function FilterLeaf(
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

    protected function RememberDaftObjectData(DefinesOwnIdPropertiesInterface $object) : void
    {
        static::ThrowIfNotType($object, DaftNestedObject::class, 1, __METHOD__);

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
        parent::ThrowIfNotType($object, $type, $argument, $function);

        if ( ! is_a($object, DaftNestedObject::class, is_string($object))) {
            throw new DaftObjectRepositoryTypeByClassMethodAndTypeException(
                $argument,
                static::class,
                $function,
                DaftNestedObject::class,
                is_string($object) ? $object : get_class($object)
            );
        }
    }
}
