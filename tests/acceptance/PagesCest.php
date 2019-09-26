<?php
declare(strict_types=1);

namespace acceptance;


use AcceptanceTester;

class PagesCest
{
    public function addPageWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/pages');

        $I->click('.btn.add');
        $I->waitForElement('#webFormId_KikCMSFormsPageForm');
        $I->seeElement('input[name="pageLanguage*:name"]');

//        $I->click('.saveAndClose');
//        $I->waitForElement('.alert');
//        $I->canSee('Not all fields are correctly filled. Please walk through the form to check for errors.');

        $I->fillField(['name' => 'pageLanguage*:name'], 'test');

//        $I->executeJS('tinyMCE.activeEditor.setContent("test");');

//        $I->fillField(['name' => 'content*:value'], 'test');
//
//        $I->executeJS('$("input[name=\'content*:value\']").val("test")');
        $I->fillTinyMceEditorByName('content*:value', '<p>test</p>');
//        $I->executeJS('tinyMCE.activeEditor.setContent("test");');

//        $I->switchToIFrame('.type-wysiwyg iframe');
//        $I->executeJS('document.getElementById("tinymce").innerHTML = "<p>Test</p>";');

        $I->wait(1);

        $I->click('.saveAndClose');

        $I->wait(6);

        $I->makeScreenshot('xxx');

//        $I->notS('#webFormId_KikCMSFormsPageForm');
    }
}