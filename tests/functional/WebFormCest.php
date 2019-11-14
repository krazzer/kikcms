<?php
declare(strict_types=1);

namespace functional;


use FunctionalTester;
use KikCMS\Models\File;

class WebFormCest
{
    public function _before(FunctionalTester $I)
    {
        $I->login();
    }

    public function testGetFilePreview(FunctionalTester $I)
    {
        $I->getDbService()->insert(File::class, ['id' => 1, 'name' => 'testfile']);
        $I->sendAjaxPostRequest('/cms/webform/filepreview/1');
        $I->see('"dimensions":null,"name":"testfile"}');
    }

    public function testGetFinderAction(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('/cms/webform/getFinder');
        $I->assertContains('id="Finder', json_decode($I->grabPageSource())->finder);
    }
}