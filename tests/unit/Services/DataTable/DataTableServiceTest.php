<?php

namespace Services\DataTable;

use Helpers\Unit;
use KikCMS\Classes\WebForm\DataForm\StorageData;
use KikCMS\Services\DataTable\DataTableService;
use KikCMS\Services\DataTable\RearrangeService;
use Website\DataTables\Persons;

class DataTableServiceTest extends Unit
{
    public function testAddDisplayOrderToStorageData()
    {
        $dataTableService = new DataTableService();
        $dataTableService->setDI($this->getDbDi());

        $rearrangeServiceMock = $this->getMockBuilder(RearrangeService::class)->getMock();

        $rearrangeServiceMock->method('getMax')->willReturn(1);
        $rearrangeServiceMock->expects($this->once())->method('makeRoomForFirst');

        $dataTable   = new Persons();
        $storageData = new StorageData();

        $dataTableService->rearrangeService = $rearrangeServiceMock;

        $dataTableService->addDisplayOrderToStorageData($dataTable, $storageData);

        $this->assertEquals(['display_order' => 2], $storageData->getMainInput());

        $dataTable->setSortableNewFirst(true);

        $dataTableService->addDisplayOrderToStorageData($dataTable, $storageData);

        $this->assertEquals(['display_order' => 1], $storageData->getMainInput());
    }
}