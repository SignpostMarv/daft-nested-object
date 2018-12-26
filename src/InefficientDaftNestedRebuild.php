<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class InefficientDaftNestedRebuild
{
    /**
    * @var DaftNestedWriteableObjectTree
    */
    protected $tree;

    /**
    * @var array<int, scalar|scalar[]>
    */
    protected $parentIdXref = [];

    /**
    * @var array<int, array<int, DaftNestedWriteableObject>>
    */
    protected $children = [[]];

    /**
    * @var array<int, scalar|scalar[]>
    */
    protected $idXref = [];

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
                $this->idXref,
                $this->children
            );
        }
    }

    protected function Reset() : void
    {
        $parentIdXref = [(array) $this->tree->GetNestedObjectTreeRootId()];

        /**
        * @var array<int, array<int, DaftNestedWriteableObject>>
        */
        $children = [[]];

        /**
        * @var array<int, scalar|scalar[]>
        */
        $idXref = [];

        $this->parentIdXref = $parentIdXref;
        $this->children = $children;
        $this->idXref = $idXref;
    }

    protected function ProcessTree() : void
    {
        $this->Reset();

        $tree = $this->tree->RecallDaftNestedObjectFullTree();

        usort($tree, function (DaftNestedWriteableObject $a, DaftNestedWriteableObject $b) : int {
            return $a->CompareToDaftSortableObject($b);
        });

        foreach ($tree as $i => $leaf) {
            $leafParentId = $leaf->ObtainDaftNestedObjectParentId();
            $pos = array_search($leafParentId, $this->parentIdXref, true);

            if (false === $pos) {
                $this->parentIdXref[] = $leafParentId;

                /**
                * @var int
                */
                $pos = array_search($leafParentId, $this->parentIdXref, true);

                $this->children[$pos] = [];
            }

            if ( ! in_array($leaf, $this->children[$pos], true)) {
                $this->children[$pos][] = $leaf;
            }

            if ( ! in_array($leaf->GetId(), $this->idXref, true)) {
                /**
                * @var scalar|scalar[]
                */
                $leafId = $leaf->GetId();
                $this->idXref[] = $leafId;
            }

            $leaf->SetIntNestedLeft(0);
            $leaf->SetIntNestedRight(0);
            $leaf->SetIntNestedLevel(0);

            $tree[$i] = $this->tree->StoreThenRetrieveFreshLeaf($leaf);
        }
    }

    protected function InefficientRebuild(
        DaftNestedWriteableObject $leaf,
        int $level,
        int $n,
        array $parents,
        array $ids,
        array $children
    ) : int {
        /**
        * @var scalar|scalar[]
        */
        $id = $leaf->GetId();

        $leaf->SetIntNestedLevel($level);
        $leaf->SetIntNestedLeft($n);

        ++$n;

        /**
        * @var int|false
        */
        $parentPos = array_search((array) $id, $parents, true);

        if (false !== $parentPos) {
            foreach ($this->children[$parentPos] as $child) {
                $n = $this->InefficientRebuild($child, $level + 1, $n, $parents, $ids, $this->children);
            }
        }

        $leaf->SetIntNestedRight($n);

        $this->tree->StoreThenRetrieveFreshLeaf($leaf);

        return $n + 1;
    }
}
