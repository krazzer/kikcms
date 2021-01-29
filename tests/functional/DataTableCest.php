<?php
declare(strict_types=1);

namespace functional;


use FunctionalTester;
use KikCMS\DataTables\Languages;
use KikCMS\DataTables\Pages;
use KikCMS\Models\File;
use KikCMS\Models\Page;
use Website\Models\DataTableTest;
use Website\Models\Person;
use Website\Models\PersonImage;

class DataTableCest
{
    public function _before(FunctionalTester $I)
    {
        $I->login();

        $I->getDbService()->insert(DataTableTest::class, [
            'id'              => 4,
            'text'            => '',
            'file_id'         => null,
            'checkbox'        => 0,
            'date'            => '2020-01-01',
            'multicheckbox'   => '',
            'datatableselect' => '',
            'textarea'        => '',
            'select'          => null,
            'hidden'          => '',
            'autocomplete'    => '',
            'password'        => '',
            'wysiwyg'         => '',
        ]);
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

    public function addImageWorks(FunctionalTester $I)
    {
        $I->getDbService()->insert(File::class, ['id' => 1, 'name' => 'testfile', 'hash' => 'abc', 'extension' => 'png']);
        $I->getDbService()->insert(File::class, ['id' => 2, 'name' => 'testfile', 'hash' => 'abc', 'extension' => 'pdf']);

        $I->sendAjaxPostRequest('/cms/datatable/addImage', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'Website\DataTables\PersonImages',
            'activeLangCode'     => 'nl',
            'fileIds'            => 1,
        ]);

        $response = (array) json_decode($I->grabPageSource());

        $I->assertArrayNotHasKey('errors', $response);
        $I->assertArrayHasKey('table', $response);

        $I->canSeeResponseCodeIs(200);

        $I->sendAjaxPostRequest('/cms/datatable/addImage', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'Website\DataTables\PersonImages',
            'activeLangCode'     => 'nl',
            'fileIds'            => 2,
        ]);

        $response = (array) json_decode($I->grabPageSource());

        $I->assertArrayHasKey('errors', $response);
    }

    public function deleteWorks(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('/cms/datatable/delete', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Pages',
            'activeLangCode'     => 'nl',
            'ids'                => [1],
        ]);

        $I->canSeeResponseCodeIs(200);

        $I->getService('acl')->addComponent(Pages::class, 'delete');
        $I->getService('acl')->deny('developer', Pages::class, 'delete');

        $I->sendAjaxPostRequest('/cms/datatable/delete', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Pages',
            'activeLangCode'     => 'nl',
            'ids'                => [1],
        ]);

        $I->canSeeResponseCodeIs(401);

        $I->getService('acl')->allow('developer', Pages::class, 'delete');

        $I->getDbService()->insert(Page::class, ['id' => 10, 'lft' => 100, 'rgt' => 103]);
        $I->getDbService()->insert(Page::class, ['id' => 11, 'parent_id' => 10, 'lft' => 101, 'rgt' => 102]);

        $I->sendAjaxPostRequest('/cms/datatable/delete', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Pages',
            'activeLangCode'     => 'nl',
            'ids'                => [10],
        ]);

        $response = (array) json_decode($I->grabPageSource());

        $I->assertArrayHasKey('error', $response);
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

    public function rearrangeWorks(FunctionalTester $I)
    {
        $I->getDbService()->insert(Person::class, ['id' => 1]);
        $I->getDbService()->insert(PersonImage::class, ['id' => 1, 'person_id' => 1, 'display_order' => 1]);
        $I->getDbService()->insert(PersonImage::class, ['id' => 2, 'person_id' => 1, 'display_order' => 2]);
        $I->getDbService()->insert(PersonImage::class, ['id' => 3, 'person_id' => 1, 'display_order' => 3]);

        $I->sendAjaxPostRequest('/cms/datatable/rearrange', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'Website\DataTables\PersonImages',
            'activeLangCode'     => 'nl',
            'id'                 => 1,
            'targetId'           => 2,
            'position'           => 'after',
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

    public function unauthorizedSaveWorks(FunctionalTester $I)
    {
        $I->getService('acl')->addComponent(Languages::class, 'edit');
        $I->getService('acl')->deny('developer', Languages::class, 'edit');

        $I->sendAjaxPostRequest('/cms/datatable/save', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Languages',
            'activeLangCode'     => 'nl',
            'id'                 => 1,
        ]);

        $I->canSeeResponseCodeIs(401);
    }

    public function unauthorizedRenderableWorks(FunctionalTester $I)
    {
        $I->getService('acl')->deny('developer', Languages::class, '*');

        $I->sendAjaxPostRequest('/cms/datatable/save', [
            'renderableInstance' => 'dataTable5dc40ab26a399',
            'renderableClass'    => 'KikCMS\DataTables\Languages',
            'activeLangCode'     => 'nl',
        ]);

        $I->canSeeResponseCodeIs(401);
    }
}