<?php

namespace unit\Services\DataTable;


use KikCMS\Services\DataTable\TableDataService;
use Phalcon\Mvc\Model\Query\Builder;
use PHPUnit\Framework\TestCase;

class TableDataServiceTest extends TestCase
{
    public function testGetHeadColumns()
    {
        $tableDataService = new TableDataService();

        // test no data
        $this->assertEquals([], $tableDataService->getHeadColumns([], [], [], new Builder));

        $tableData = [[
            'field1' => 'value1.1',
            'field2' => 'value2.1',
        ], [
            'field1' => 'value1.1',
            'field2' => 'value2.1',
        ]];

        $result = [
            'field1' => 'field1',
            'field2' => 'field2'
        ];

        $this->assertEquals($result, $tableDataService->getHeadColumns($tableData, [], [], new Builder));

        $query = (new Builder)->columns(['a.field1', 'b.field2']);

        $result = [
            'a.field1' => 'field1',
            'b.field2' => 'field2',
        ];

        $aliases = ['a', 'b'];

        $this->assertEquals($result, $tableDataService->getHeadColumns($tableData, [], $aliases, $query));

        $fieldMap = [
            'field1' => 'FieldOne',
            'field2' => 'FieldTwo',
        ];

        $result = [
            'a.field1' => 'FieldOne',
            'b.field2' => 'FieldTwo',
        ];

        $this->assertEquals($result, $tableDataService->getHeadColumns($tableData, $fieldMap, $aliases, $query));
    }
}
