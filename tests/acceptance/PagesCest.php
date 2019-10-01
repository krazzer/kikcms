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

        // click add page
        $I->click('.btn.add');
        $I->waitForElement('#webFormId_KikCMSFormsPageForm');
        $I->seeElement('input[name="pageLanguage*:name"]');

        // save and close, fail because fields aren't filled
        $I->click('.saveAndClose');
        $I->waitForElement('.alert');
        $I->canSee('Not all fields are correctly filled. Please walk through the form to check for errors.');

        // save page
        $I->fillField(['name' => 'pageLanguage*:name'], 'test');
        $I->executeJS('$("textarea[name=\'content*:value\']").val("test")');

        $I->click('.saveAndClose');
        $I->waitForElement('.table tr:nth-child(5)');
        $I->wait(1);

        // remove page
        $I->click('.table tr:nth-child(5)');
        $I->click('.btn.delete');
        $I->acceptPopup();

        $I->waitForJS("return $.active == 0;", 10);

        $I->dontSeeElement('.table tr:nth-child(5)');
    }

    public function switchTemplateWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/pages');

        $I->doubleClick('.table tr:nth-child(2)');
        $I->waitForElement('#webFormId_KikCMSFormsPageForm');

        $I->click('div[data-tab=advanced]');
        $I->selectOption('form #template', 'home');
        $I->waitForJS("return $.active == 0;", 10);

        $I->click('div[data-tab="0"]');
        $I->dontSeeElement('.type-wysiwyg');
    }
}