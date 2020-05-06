<?php
declare(strict_types=1);

namespace acceptance;


use AcceptanceTester;
use Facebook\WebDriver\Exception\WebDriverException;

class DataTableCest
{
    public function uploadImagesDirectlyWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/test/personimages');

        $I->canSeeNumberOfElements('.table tbody tr', 0);

        $I->attachFile('input[type="file"]', 'test-upload-file.png');
        $I->waitForJS("return $.active == 0;", 300);

        $I->canSeeNumberOfElements('.table tbody tr', 1);

        $I->click('.table tbody tr');
        $I->wait(1);

        try {
            $I->click('.btn.delete');
            $I->acceptPopup();
        } catch (WebDriverException $e) {
            // ignore "unexpected alert open"
        }

        $I->waitForJS("return $.active == 0;", 300);
        $I->wait(1);

        $I->canSeeNumberOfElements('.table tbody tr', 0);

        // remove files
        $I->amOnPage('/cms/media');

        $I->click('.files-container .file');
        $I->wait(1);

        try {
            $I->click('.btn.delete');
            $I->acceptPopup();
        } catch (WebDriverException $e) {
            // ignore "unexpected alert open"
        }

        $I->waitForJS("return $.active == 0;", 300);
        $I->wait(1);

        $I->canSeeNumberOfElements('.files-container .file', 0);
    }
}