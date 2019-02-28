<?php

namespace KikCMS\Services\DataTable;


use PHPUnit\Framework\TestCase;

class TinyMceServiceTest extends TestCase
{
    public function testGetLinkListByUrlData()
    {
        $tinyMceService = (new TinyMceService);

        $urlData = [[
            'id'        => 1,
            'parent_id' => null,
            'name'      => 'testMenu',
            'slug'      => 'menu',
            'type'      => 'menu'
        ], [
            'id'        => 2,
            'parent_id' => 1,
            'name'      => 'test',
            'slug'      => 'test',
            'type'      => 'page'
        ]];

        $expectedResult = [[
            'id'        => 1,
            'parent_id' => null,
            'name'      => 'testMenu',
            'slug'      => 'menu',
            'type'      => 'menu',
            'menu'      => [[
                'id'        => 2,
                'parent_id' => 1,
                'name'      => 'test',
                'slug'      => 'test',
                'type'      => 'page',
                'value'     => '/test',
                'title'     => 'test'
            ]],
            'value'     => '',
            'title'     => 'testMenu'
        ]];

        $result = $tinyMceService->getLinkListByUrlData($urlData);

        $this->assertEquals($expectedResult, $result);
    }
}
