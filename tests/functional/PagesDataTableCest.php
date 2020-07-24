<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;

class PagesDataTableCest
{
    public function _before(FunctionalTester $I)
    {
        $I->login();
    }

    public function treeOrderWorks(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('/cms/datatable/pages/tree-order', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Pages',
            'activeLangCode'     => 'nl',
            'id'                 => '3',
            'targetId'           => '4',
            'position'           => 'after',
        ]);

        $result = $I->getDbService()->queryValues('SELECT id FROM cms_page WHERE parent_id = 5 ORDER BY display_order');

        $I->assertEquals([4, 3], $result);

        $I->sendAjaxPostRequest('/cms/datatable/pages/tree-order', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Pages',
            'activeLangCode'     => 'nl',
            'id'                 => '3',
            'targetId'           => '4',
            'position'           => 'before',
        ]);

        $result = $I->getDbService()->queryValues('SELECT id FROM cms_page WHERE parent_id = 5 ORDER BY display_order');

        $I->assertEquals([3, 4], $result);

        $I->sendAjaxPostRequest('/cms/datatable/pages/tree-order', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Pages',
            'activeLangCode'     => 'nl',
            'id'                 => '6',
            'targetId'           => '5',
            'position'           => 'into',
        ]);

        $result = $I->getDbService()->queryValues('SELECT id FROM cms_page WHERE parent_id = 5 ORDER BY display_order');

        $I->assertEquals([3, 4, 6], $result);
    }
}