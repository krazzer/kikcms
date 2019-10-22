<?php

use GuzzleHttp\Client;

class LoginCest
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
        $I->addUser();
        $I->login();
        $I->seeElement('#menu');

        $I->amOnPage('/cms/logout');
        $I->dontSeeElement('#menu');
    }

    public function loginWrongPassWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->login(AcceptanceTester::TEST_USERNAME, 'wrongPass', null, false);
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

        $resetPassUrl = $this->getResetPasswordUrlFromEmail();

        $I->amOnUrl($resetPassUrl);

        $I->seeElement('#webFormId_KikCMSFormsPasswordResetForm');

        $I->submitForm('#login-form form', [
            'password'        => 'myNewPassword1',
            'password_repeat' => 'myNewPassword1',
        ]);

        $I->login($I::TEST_USERNAME, 'myNewPassword1', null, false);

        $I->seeElement('#menu');

        // delete all emails
        (new Client(['base_uri' => 'http://mailtest:8025']))->delete('api/v1/messages');
    }

    /**
     * @return string
     */
    private function getResetPasswordUrlFromEmail(): string
    {
        $mailCatcher = new Client(['base_uri' => 'http://mailtest:8025']);

        $message  = json_decode($mailCatcher->get('api/v2/search?query=Password&kind=containing&limit=1')->getBody());
        $mailBody = quoted_printable_decode($message->items[0]->MIME->Parts[1]->Body);

        return explode("\r\n\r\n", $mailBody)[1];
    }
}
