<?php

namespace KikCMS\Classes\DataTable;


use PHPUnit\Framework\TestCase;
use stdClass;

class TableDataTest extends TestCase
{
    public function testGetRowDisplayValues()
    {
        $tableData = new TableData();

        $tableData->setData([1 => ['col1' => 'val1', 'col2' => 'val2']]);

        // test no displayMap
        $this->assertEquals(['col1' => 'val1', 'col2' => 'val2'], $tableData->getRowDisplayValues(1));

        // test with displayMap
        $tableData->setDisplayMap(['col1' => 'ColOne', 'col2' => 'ColTwo']);
        $this->assertEquals(['col1' => 'val1', 'col2' => 'val2'], $tableData->getRowDisplayValues(1));

        // test with aliases
        $tableData->setDisplayMap(['a.col1' => 'ColOne', 'a.col2' => 'ColTwo']);

        $object = new StdClass();

        $object->col1 = 'val1';
        $object->col2 = 'val2';

        $tableData->setData([1 => ['a' => $object]]);

        $this->assertEquals(['a.col1' => 'val1', 'a.col2' => 'val2'], $tableData->getRowDisplayValues(1));
    }
}
