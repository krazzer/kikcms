<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;

class CmsCest
{
    public function _before(FunctionalTester $I)
    {
        $I->login();
    }

    public function filePickerWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/filepicker');
        $I->seeElement('.files-container');
    }

    public function mediaWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/media');
        $I->seeElement('.files-container');
    }

    public function settingsWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/settings');
        $I->seeElement('.settingsform');
    }

    public function usersWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/users');
        $I->seeElement('.datatable');
    }

    public function statsWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/stats');
        $I->seeElement('#visitors');
    }

    public function previewWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/preview/4');
        $I->see('Lorem ipsum dolor sit amet');
    }

    public function getTinyMceLinksWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/getTinyMceLinks');
        $I->see('[{"id":6,"parent_id":null');
    }

    public function getTranslationsForKeyWorks(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('/cms/getTranslationsForKey', ['key' => 'cms.roles.admin']);
        $I->see('{"en":"Administrator","nl":"Administrator"}');
    }

    public function getUrlsWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/get-urls/en');
        $I->see('["\/","\/pagina-2"]');
    }

    public function generateSecurityTokenWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/generate-security-token');
        $I->see('securityToken');
    }
}