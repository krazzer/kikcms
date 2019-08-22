<?php 

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
}
