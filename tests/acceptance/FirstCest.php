<?php 

class FirstCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function frontpageWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/cms/login');
        $I->seeElement('#login');
    }
}
