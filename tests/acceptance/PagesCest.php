<?php
declare(strict_types=1);

namespace acceptance;


use AcceptanceTester;
use Facebook\WebDriver\Remote\DriverCommand;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

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

        $I->doubleClick('.table tr:nth-child(3)');
        $I->waitForElement('#webFormId_KikCMSFormsPageForm');

        $I->click('div[data-tab=advanced]');
        $I->selectOption('form #template', 'home');
        $I->waitForJS("return $.active == 0;", 10);

        $I->click('div[data-tab="0"]');
        $I->dontSeeElement('.type-wysiwyg');
    }

    public function switchLanguageWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/pages');

        $I->dontSee('Pagina 2');

        $I->selectOption('select[name="language"]', 'nl');
        $I->waitForJS("return $.active == 0;", 10);

        $I->see('Pagina 2');
    }

    public function editMenuWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/pages');

        $I->click('.table tr:nth-child(1) .edit');
        $I->waitForElement('#webFormId_KikCMSFormsMenuForm');
        $I->click('.saveAndClose');
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

    public function dragAndDropWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/pages');

        $I->executeInSelenium(function (RemoteWebDriver $webDriver) {
            $element = $webDriver->findElement(WebDriverBy::cssSelector('tr[data-id="6"] span.name'));
//            $webDriver->execute(DriverCommand::MOVE_TO, [$element->getCoordinates(), 0, 0]);

//            $webDriver->action()->moveToElement($element, 10, 10);
//            $webDriver->execute(DriverCommand::MOUSE_DOWN);

            $webDriver->action()->clickAndHold($element);
            $webDriver->action()->moveByOffset(0, -100);
            $webDriver->execute(DriverCommand::MOUSE_UP);

        });

        $I->wait(1);

        $I->makeScreenshot(1);

//        $I->dragAndDrop('tr[data-id="6"] span.name', 'tr[data-id="3"]');
//        $I->makeScreenshot(1);
//
//        $I->moveMouseOver();
//        $I->performOn();
    }
}