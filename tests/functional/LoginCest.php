<?php
declare(strict_types=1);

namespace functional;

use AcceptanceTester;
use FunctionalTester;
use GuzzleHttp\Client;
use KikCMS\Models\User;

class LoginCest
{
    public function loginPageWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms');
        $I->seeElement('#login-form');
    }

    public function loginWorks(FunctionalTester $I)
    {
        $I->login();
        $I->seeElement('#menu');

        $I->amOnPage('/cms/logout');
        $I->dontSeeElement('#menu');
    }

    public function loginWrongPassWorks(FunctionalTester $I)
    {
        $I->login(AcceptanceTester::TEST_USERNAME, 'wrongPass');
        $I->seeElement('#login');
        $I->seeElement('.alert');
        $I->dontSeeElement('#menu');
    }

    public function lostPassPageWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/login/reset');
        $I->seeElement('#webFormId_KikCMSFormsPasswordResetLinkForm');
    }

    public function lostPassSendWorks(FunctionalTester $I)
    {
        $I->addUser();
        $I->amOnPage('/cms/login/reset');

        $I->submitForm('#login-form form', ['email' => $I::TEST_USERNAME]);

        $resetPassUrl = $this->getResetPasswordUrlFromEmail();

        $I->amOnPage($resetPassUrl);

        $I->seeElement('#webFormId_KikCMSFormsPasswordResetForm');

        $I->submitForm('#login-form form', [
            'password'        => 'myNewPassword1',
            'password_repeat' => 'myNewPassword1',
        ]);

        // reset validation to prevent error from old forms
        $I->getApplication()->validation->setValidators([]);

        $I->login($I::TEST_USERNAME, 'myNewPassword1', false);

        $I->seeElement('#menu');

        // delete all emails
        (new Client(['base_uri' => 'http://mailtest:8025']))->delete('api/v1/messages');
    }

    public function loginNotActivatedWorks(FunctionalTester $I)
    {
        $I->getDbService()->insert(User::class, [
            User::FIELD_EMAIL    => 'noact',
            User::FIELD_ROLE     => 'developer',
            User::FIELD_ID       => 2,
        ]);

        $I->amOnPage('/cms/login');

        $I->login('noact', '', false);

        $I->see('Your account is not active yet');
    }

    public function loginBlockedWorks(FunctionalTester $I)
    {
        $I->addUser(true);

        $I->amOnPage('/cms/login');

        $I->login($I::TEST_USERNAME, $I::TEST_PASS, false);

        $I->see('your account has been blocked');
    }

    /**
     * @return string
     */
    private function getResetPasswordUrlFromEmail(): string
    {
        $mailCatcher = new Client(['base_uri' => 'http://mailtest:8025']);
        $message  = json_decode($mailCatcher->get('api/v2/search?query=Password&kind=containing&limit=1')->getBody()->getContents());
        $mailBody = quoted_printable_decode($message->items[0]->MIME->Parts[1]->Body);

        return parse_url(explode("\r\n\r\n", $mailBody)[1])['path'];
    }
}