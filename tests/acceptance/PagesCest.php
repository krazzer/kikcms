<?php
declare(strict_types=1);

namespace acceptance;


use AcceptanceTester;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class PagesCest
{
    public function dragAndDropWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/pages');

        $I->cantSeeElement('tr[data-id="6"][data-level="1"]');

        $I->executeInSelenium(function (RemoteWebDriver $webDriver) {
            $webDriver->action()
                ->moveByOffset(340, 340)
                ->clickAndHold()
                ->moveByOffset(10, 10)
                ->moveByOffset(-10, -100)->perform();
        });

        $I->wait(1);

        $I->executeInSelenium(function (RemoteWebDriver $webDriver) {
            $webDriver->action()->release()->perform();
        });

        $I->waitForJS("return $.active == 0;", 300);
        $I->wait(1);
        $I->canSeeNumberOfElements('.table tr', 5);
//        $I->canSeeElement('tr[data-id="6"][data-level="1"]');
        $I->updateInDatabase('cms_page', ['lft' => null, 'rgt' => null, 'parent_id' => null, 'level' => 0,], ['id' => 6]);
    }

    public function addPageWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();

        // clear cache
        $I->amOnPage('/cms/cache/empty');
        $I->amOnPage('/cms/pages');

        // click add page
        $I->click('.btn.add');
        $I->waitForJS("return $.active == 0;", 300);
        $I->waitForElement('#webFormId_KikCMSFormsPageForm');
        $I->seeElement('input[name="pageLanguage*:name"]');

        // save and close, fail because fields aren't filled
        $I->click('.saveAndClose');
        $I->waitForJS("return $.active == 0;", 300);
        $I->waitForElement('.alert');
        $I->canSee('Not all fields are correctly filled. Please walk through the form to check for errors.');

        // save page
        $I->fillField(['name' => 'pageLanguage*:name'], 'test');
        $I->executeJS('$("textarea[name=\'content*:value\']").val("test")');

        $I->click('.saveAndClose');
        $I->waitForJS("return $.active == 0;", 300);
        $I->waitForElement('.table tr:nth-child(5)');
        $I->wait(1);

        // remove page
        $I->click('.table tr:nth-child(5)');

        try {
            $I->click('.btn.delete');
            $I->acceptPopup();
        } catch (WebDriverException $e) {
            // ignore "unexpected alert open"
        }

        $I->waitForJS("return $.active == 0;", 300);
        $I->dontSeeElement('.table tr:nth-child(5)');
    }

    public function switchTemplateWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/pages');

        $I->doubleClick('.table tr:nth-child(3)');
        $I->waitForJS("return $.active == 0;", 300);
        $I->waitForElement('#webFormId_KikCMSFormsPageForm');

        $I->click('div[data-tab=advanced]');
        $I->selectOption('form #template', 'home');
        $I->waitForJS("return $.active == 0;", 300);

        $I->click('div[data-tab="0"]');
        $I->dontSeeElement('.type-wysiwyg');
    }

    public function switchLanguageWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/pages');

        $I->dontSee('Pagina 2 NL');

        $I->selectOption('select[name="language"]', 'nl');
        $I->waitForJS("return $.active == 0;", 300);

        $I->see('Pagina 2 NL');

        $I->selectOption('select[name="language"]', 'en');
        $I->waitForJS("return $.active == 0;", 300);
    }

    public function editMenuWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/pages');

        $I->moveMouseOver(['css' => '.table tbody tr:nth-child(1)']);
        $I->click('.table tbody tr:nth-child(1) .action.edit');
        $I->waitForJS("return $.active == 0;", 300);
        $I->waitForElement('#webFormId_KikCMSFormsMenuForm');
        $I->click('.saveAndClose');
        $I->waitForJS("return $.active == 0;", 300);
        $I->waitForElement('.table tr:nth-child(1).edited');
    }

    public function collapseExpandWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/pages');

        $I->canSeeNumberOfElements('.table tr', 5);

        $I->click('.table tr:nth-child(1) .arrow');

        $I->canSeeNumberOfElements('.table tr', 3);

        $I->click('.table tr:nth-child(1) .arrow');

        $I->canSeeNumberOfElements('.table tr', 5);
    }
}