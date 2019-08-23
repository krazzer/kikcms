<?php declare(strict_types=1);

namespace KikCMS\Classes\Page;

/**
 * Convert an array containing parent child to an array with nested set
 */
class AdjacencyToNestedSet
{
    /** @var int */
    private $total;

    /** @var array [id => array childIds] */
    private $relations;

    /** @var array [id => [left, right, level]] */
    private $result = [];

    /**
     * @param array $relations
     */
    public function __construct(array $relations)
    {
        $this->total     = 0;
        $this->relations = $relations;
    }

    /**
     * @param int $parentId
     * @param int $level
     */
    public function traverse(int $parentId = 0, int $level = -1)
    {
        $left = $this->total;
        $this->total++;

        $childIds = $this->getChildren($parentId);

        if ($childIds) {
            foreach ($childIds as $childId) {
                $this->traverse($childId, $level + 1);
            }
        }

        $right = $this->total;
        $this->total++;

        $this->save($parentId, $left, $right, $level);
    }

    /**
     * @param bool $removeRoot
     * @return array
     */
    public function getResult(bool $removeRoot = true): array
    {
        $result = $this->result;

        if ($removeRoot) {
            unset($result[0]);
        }

        return $result;
    }

    /**
     * @param int $parentId
     * @return mixed
     */
    private function getChildren(int $parentId)
    {
        return $this->relations[$parentId];
    }

    /**
     * @param int $id
     * @param int $left
     * @param int $right
     * @param int $level
     */
    private function save(int $id, int $left, int $right, int $level)
    {
        $this->result[$id] = [$left, $right, $level];
    }
}