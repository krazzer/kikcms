<?php

class RememberMeCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function rememberMeWorks(AcceptanceTester $I)
    {
        $I->addUser();
        $I->loginAndRemember();
        $I->seeElement('#menu');

        $data = $I->grabCookie('remember-me-443');

        $I->resetSession();

        $I->amOnPage('/cms/pages');
        $I->setCookie('remember-me-443', $data);
        $I->amOnPage('/cms/pages');
        $I->seeElement('#menu');
    }
}
