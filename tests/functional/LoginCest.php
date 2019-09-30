<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;

class LoginCest
{
    public function loginPageWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms');
        $I->seeElement('#login-form');
    }
}