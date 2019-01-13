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
            * @var array<int, scalar|scalar[]>
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
                return ! TypeParanoia::MaybeInArray($leaf->GetId(), $outIds);
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
    * @param mixed $id
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
    * @param mixed $id
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
    * @param mixed $id
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
    * @param mixed $id
    */
    public function CountDaftNestedObjectPathToId($id, bool $includeLeaf) : int
    {
        return count($this->RecallDaftNestedObjectPathToId($id, $includeLeaf));
    }

    public function RememberDaftObjectData(
        DefinesOwnIdPropertiesInterface $object,
        bool $assumeDoesNotExist = self::BOOL_DEFAULT_ASSUME_DOES_NOT_EXIST
    ) : void {
        NestedTypeParanoia::ThrowIfNotNestedType(
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
            NestedTypeParanoia::ThrowIfNotNestedType(
                $type,
                1,
                static::class,
                __FUNCTION__
            );

        return parent::DaftObjectRepositoryByType($type, ...$args);
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
