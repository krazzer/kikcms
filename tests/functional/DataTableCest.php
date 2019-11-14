<?php
declare(strict_types=1);

namespace functional;


use FunctionalTester;

class DataTableCest
{
    public function _before(FunctionalTester $I)
    {
        $I->login();
    }

    public function addWorks(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('/cms/datatable/add', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Languages',
            'activeLangCode'     => 'nl',
        ]);

        $I->canSeeResponseCodeIs(200);
    }

    public function deleteWorks(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('/cms/datatable/delete', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Pages',
            'activeLangCode'     => 'nl',
            'id'                 => 1,
        ]);

        $I->canSeeResponseCodeIs(200);
    }

    public function checkCheckboxWorks(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('/cms/datatable/checkCheckbox', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'Website\DataTables\DataTableTestObjects',
            'activeLangCode'     => 'nl',
            'editId'             => 4,
            'column'             => 'checkbox',
            'checked'            => 1,
        ]);

        $I->canSeeResponseCodeIs(200);
        $I->see('true');
    }

    public function editWorks(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('/cms/datatable/edit', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Languages',
            'activeLangCode'     => 'nl',
            'id'                 => 1,
        ]);

        $I->canSeeResponseCodeIs(200);
    }

    public function searchWorks(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('/cms/datatable/search', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Languages',
            'activeLangCode'     => 'nl',
            'search'             => 'searchValue',
        ]);

        $I->canSeeResponseCodeIs(200);
    }

    public function sortWorks(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('/cms/datatable/sort', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Languages',
            'activeLangCode'     => 'nl',
            'sortDirection'      => 'asc',
            'sortColumn'         => 'code',
        ]);

        $I->canSeeResponseCodeIs(200);
    }

    public function pageWorks(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('/cms/datatable/page', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Languages',
            'activeLangCode'     => 'nl',
            'page'               => 2,
        ]);

        $I->canSeeResponseCodeIs(200);
    }
}