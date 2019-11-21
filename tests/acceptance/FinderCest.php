<?php
declare(strict_types=1);

namespace acceptance;


use AcceptanceTester;

class FinderCest
{
    public function uploadWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login();
        $I->amOnPage('/cms/media');

        $I->attachFile('input[type="file"]', 'test-upload-file.png');
        $I->waitForJS("return $.active == 0;", 30);

        $I->see('test-upload-file.png');
        $I->canSeeNumberOfElements('.files-container .file', 1);

        $I->click('.files-container .file');
        $I->click('.btn.delete');
        $I->acceptPopup();

        $I->waitForJS("return $.active == 0;", 30);
        $I->dontSeeElement('.files-container .file');
    }
}