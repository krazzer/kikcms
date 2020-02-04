<?php
declare(strict_types=1);

namespace acceptance;


use AcceptanceTester;
use Facebook\WebDriver\Exception\WebDriverException;

class FinderCest
{
    public function uploadWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/media');

        $I->attachFile('input[type="file"]', 'test-upload-file.png');
        $I->waitForJS("return $.active == 0;", 300);

        $I->see('test-upload-file.png');
        $I->canSeeNumberOfElements('.files-container .file', 1);

        $I->click('.files-container .file');

        try {
            $I->click('.btn.delete');
            $I->acceptPopup();
        } catch (WebDriverException $e) {
            // ignore "unexpected alert open"
        }

        $I->waitForJS("return $.active == 0;", 300);
        $I->dontSeeElement('.files-container .file');
    }

    public function webFormUploadWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();

        $I->amOnPage('/cms/test/personform');
        $I->attachFile('input[type="file"]', 'test-upload-file.png');
        $I->waitForJS("return $.active == 0;", 300);

        $I->seeElement('.type-file .thumb img');
        $I->see('(test-upload-file.png)');

        $I->click('.type-file .thumb');
        $I->waitForJS("return $.active == 0;", 300);
        $I->wait(1);

        $I->see('test-upload-file.png');
        $I->canSeeNumberOfElements('.files-container .file', 1);

        // remove
        $I->amOnPage('/cms/media');

        $I->click('.files-container .file');

        try {
            $I->click('.btn.delete');
            $I->acceptPopup();
        } catch (WebDriverException $e) {
            // ignore "unexpected alert open"
        }

        $I->waitForJS("return $.active == 0;", 300);
        $I->dontSeeElement('.files-container .file');
    }
}