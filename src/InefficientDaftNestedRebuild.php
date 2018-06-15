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
    private $tree;

    public function __construct(DaftNestedWriteableObjectTree $tree)
    {
        $this->tree = $tree;
    }

    public function RebuildTree() : void
    {
        $parentIdXref = [(array) $this->tree->GetNestedObjectTreeRootId()];

        /**
        * @var array<int, array<int, DaftNestedWriteableObject>> $children
        */
        $children = [[]];

        /**
        * @var array<int, scalar|scalar[]> $idXref
        */
        $idXref = [];

        $tree = $this->tree->RecallDaftNestedObjectFullTree();

        usort($tree, function (DaftNestedWriteableObject $a, DaftNestedWriteableObject $b) : int {
            return $this->tree->CompareObjects($a, $b);
        });

        /**
        * @var DaftNestedWriteableObject $leaf
        */
        foreach ($tree as $i => $leaf) {
            $leafParentId = $leaf->ObtainDaftNestedObjectParentId();
            $pos = array_search($leafParentId, $parentIdXref, true);

            if (false === $pos) {
                $parentIdXref[] = $leafParentId;

                /**
                * @var int $pos
                */
                $pos = array_search($leafParentId, $parentIdXref, true);

                $children[$pos] = [];
            }

            if ( ! in_array($leaf, $children[$pos], true)) {
                $children[$pos][] = $leaf;
            }

            if ( ! in_array($leaf->GetId(), $idXref, true)) {
                /**
                * @var scalar|scalar[] $leafId
                */
                $leafId = $leaf->GetId();
                $idXref[] = $leafId;
            }

            $leaf->SetIntNestedLeft(0);
            $leaf->SetIntNestedRight(0);
            $leaf->SetIntNestedLevel(0);

            $tree[$i] = $this->tree->StoreThenRetrieveFreshLeaf($leaf);
        }

        $n = 0;

        /**
        * @var DaftNestedWriteableObject $rootLeaf
        */
        foreach ($children[0] as $rootLeaf) {
            $n = $this->InefficientRebuild($rootLeaf, 0, $n, $parentIdXref, $idXref, $children);
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
        * @var scalar|scalar[] $id
        */
        $id = $leaf->GetId();

        $leaf->SetIntNestedLevel($level);
        $leaf->SetIntNestedLeft($n);

        ++$n;

        /**
        * @var int|false $parentPos
        */
        $parentPos = array_search((array) $id, $parents, true);

        if (false !== $parentPos) {
            /**
            * @var DaftNestedWriteableObject $child
            */
            foreach ($children[$parentPos] as $child) {
                $n = $this->InefficientRebuild($child, $level + 1, $n, $parents, $ids, $children);
            }
        }

        $leaf->SetIntNestedRight($n);

        $this->tree->StoreThenRetrieveFreshLeaf($leaf);

        return $n + 1;
    }
}
