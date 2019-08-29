<?php

use Codeception\PHPUnit\TestCase;
use GuzzleHttp\Client;

class LoginCest extends TestCase
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function loginpageWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/cms/login');
        $I->seeElement('#login');
    }

    public function loginWorks(AcceptanceTester $I)
    {
        $I->login();
        $I->seeElement('#menu');
    }

    public function loginWrongPassWorks(AcceptanceTester $I)
    {
        $I->login(AcceptanceTester::TEST_USERNAME, 'wrongPass');
        $I->seeElement('#login');
        $I->seeElement('.alert');
        $I->dontSeeElement('#menu');
    }

    public function lostPassPageWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/cms/login/reset');
        $I->seeElement('#webFormId_KikCMSFormsPasswordResetLinkForm');
    }

    public function lostPassSendWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->amOnPage('/cms/login/reset');

        $I->submitForm('#login-form form', [
            'email' => $I::TEST_USERNAME,
        ]);

        $mailCatcher = new Client(['base_uri' => 'http://mail:8025']);

        $message = json_decode($mailCatcher->get('api/v2/search?query=Password&kind=containing&limit=1')->getBody());

        $this->assertContains('Password reset / activation', $message->items[0]->Content->Body);
    }

    public function rememberMeWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/cms/login');
        $I->loginAndRemember();
    }
}
