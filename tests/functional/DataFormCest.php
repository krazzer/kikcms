<?php


namespace functional;


use FunctionalTester;

class DataFormCest
{
    public function renderWorks(FunctionalTester $I)
    {
        $I->login();

        //TestUserPass
        $I->amOnPage('/cms/test/personform');
        $I->seeElement('#webFormId_WebsiteFormsPersonForm');
    }
}