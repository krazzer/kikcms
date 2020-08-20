<?php

namespace unit\Services\Util;

use KikCMS\Services\Util\QueryService;
use Phalcon\Mvc\Model\Query\Builder;
use PHPUnit\Framework\TestCase;

class QueryServiceTest extends TestCase
{
    public function testGetAliases()
    {
        $queryService = new QueryService();

        $query = new Builder();

        // query has no aliases
        $this->assertEquals([], $queryService->getAliases($query));

        $query->from(['a1' => 'table']);

        // query 1 'from' alias
        $this->assertEquals(['a1'], $queryService->getAliases($query));

        $query->join('model', 'cond', 'a2');
        $query->join('model', 'cond', 'a3');

        // query 1 'from' alias
        $this->assertEquals(['a1', 'a2', 'a3'], $queryService->getAliases($query));
    }
}
