<?php

namespace unit\Services\Util;


use KikCMS\Services\Util\PaginateListService;
use PHPUnit\Framework\TestCase;

class PaginateListServiceTest extends TestCase
{
    public function testGetPageList()
    {
        $paginateListService = new PaginateListService();

        $this->assertEquals([1,2,3], $paginateListService->getPageList(3,1));
        $this->assertEquals([1,null,4,5,6,null,10], $paginateListService->getPageList(10,5));
        $this->assertEquals([1,2,3,4,5,null,10], $paginateListService->getPageList(10,1));
        $this->assertEquals([1,null,6,7,8,9,10], $paginateListService->getPageList(10,10));
        $this->assertEquals([1,2,3,4,5,6,7], $paginateListService->getPageList(7,1));
    }
}
