<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DaftNestedWriteableObject
* @template TRepo as DaftNestedWriteableObjectTree
*/
class InefficientDaftNestedRebuild
{
    const INT_RESET_NESTED_VALUE = 0;

    /**
    * @var DaftNestedWriteableObjectTree
    *
    * @psalm-var TRepo
    */
    protected $tree;

    /**
    * @var array<int, scalar|(scalar|array|object|null)[]>
    */
    protected $parentIdXref = [];

    /**
    * @var array<int, array<int, DaftNestedWriteableObject>>
    *
    * @psalm-var array<int, array<int, T>>
    */
    protected $children = [[]];

    /**
    * @var array<int, scalar|(scalar|array|object|null)[]>
    */
    protected $idXref = [];

    /**
    * @psalm-param TRepo $tree
    */
    public function __construct(DaftNestedWriteableObjectTree $tree)
    {
        $this->tree = $tree;
    }

    public function RebuildTree() : void
    {
        $this->ProcessTree();

        $n = 0;

        foreach ($this->children[0] as $rootLeaf) {
            $n = $this->InefficientRebuild(
                $rootLeaf,
                0,
                $n,
                $this->parentIdXref,
                $this->idXref
            );
        }
    }

    private function Reset() : void
    {
        $parentIdXref = [$this->tree->GetNestedObjectTreeRootId()];

        /**
        * @var array<int, array<int, DaftNestedWriteableObject>>
        *
        * @psalm-var array<int, array<int, T>>
        */
        $children = [[]];

        /**
        * @var array<int, scalar|(scalar|array|object|null)[]>
        */
        $idXref = [];

        $this->parentIdXref = $parentIdXref;
        $this->children = $children;
        $this->idXref = $idXref;
    }

    private function ProcessTree() : void
    {
        $this->Reset();

        /**
        * @var array<int, DaftNestedWriteableObject>
        *
        * @psalm-var array<int, T>
        */
        $tree = $this->tree->RecallDaftNestedObjectFullTree();

        usort(
            $tree,
            /**
            * @psalm-param T $a
            * @psalm-param T $b
            */
            function (DaftNestedWriteableObject $a, DaftNestedWriteableObject $b) : int {
                return $a->CompareToDaftSortableObject($b);
            }
        );

        foreach ($tree as $i => $leaf) {
            $leafParentId = $leaf->GetDaftNestedObjectParentId();
            $pos = NestedTypeParanoia::MaybeFoundInArray($leafParentId, $this->parentIdXref);

            if (is_null($pos)) {
                $this->parentIdXref[] = $leafParentId;

                /**
                * @var int
                */
                $pos = NestedTypeParanoia::MaybeFoundInArray($leafParentId, $this->parentIdXref);

                $this->children[$pos] = [];
            }

            if ( ! in_array($leaf, $this->children[$pos], true)) {
                $this->children[$pos][] = $leaf;
            }

            /**
            * @var scalar|(scalar|array|object|null)[]
            */
            $leafId = $leaf->GetId();

            if ( ! in_array($leafId, $this->idXref, true)) {
                $this->idXref[] = $leafId;
            }

            $leaf->SetIntNestedLeft(self::INT_RESET_NESTED_VALUE);
            $leaf->SetIntNestedRight(self::INT_RESET_NESTED_VALUE);
            $leaf->SetIntNestedLevel(self::INT_RESET_NESTED_VALUE);

            $tree[$i] = $this->tree->StoreThenRetrieveFreshLeaf($leaf);
        }
    }

    /**
    * @psalm-param T $leaf
    */
    private function InefficientRebuild(
        DaftNestedWriteableObject $leaf,
        int $level,
        int $n,
        array $parents,
        array $ids
    ) : int {
        /**
        * @var scalar|(scalar|array|object|null)[]
        */
        $id = $leaf->GetId();

        $leaf->SetIntNestedLevel($level);
        $leaf->SetIntNestedLeft($n);

        ++$n;

        /**
        * @var int|false
        */
        $parentPos = NestedTypeParanoia::MaybeFoundInArray((array) $id, $parents);

        if (is_int($parentPos)) {
            foreach ($this->children[$parentPos] as $child) {
                $n = $this->InefficientRebuild($child, $level + 1, $n, $parents, $ids);
            }
        }

        $leaf->SetIntNestedRight($n);

        $this->tree->StoreThenRetrieveFreshLeaf($leaf);

        return $n + 1;
    }
}
