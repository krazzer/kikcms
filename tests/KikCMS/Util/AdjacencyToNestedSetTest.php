<?php

namespace KikCMS\Util;


use PHPUnit\Framework\TestCase;

class AdjacencyToNestedSetTest extends TestCase
{
    public function testTraverse()
    {
        $relations = [
            0 => [1, 2],
            1 => [3, 4, 5],
            2 => [6, 7, 8],
            3 => [],
            4 => [],
            5 => [],
            6 => [],
            7 => [],
            8 => [],
        ];

        $result = [
            1 => [1, 8, 0],
            2 => [9, 16, 0],
            3 => [2, 3, 1],
            4 => [4, 5, 1],
            5 => [6, 7, 1],
            6 => [10, 11, 1],
            7 => [12, 13, 1],
            8 => [14, 15, 1],
        ];

        $converter = new AdjacencyToNestedSet($relations);
        $converter->traverse();

        $nestedSetStructure = $converter->getResult();
        $this->assertEquals($result, $nestedSetStructure);
    }
}
