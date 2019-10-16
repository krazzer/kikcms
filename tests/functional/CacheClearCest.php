<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;

class CacheClearCest
{
    public function clearWorks(FunctionalTester $I)
    {
        $token = $I->getService('cmsService')->createSecurityToken();

        $I->amOnPage('/cache/clear/' . $token);
        $I->see('{"success":true}');

        $I->amOnPage('/cache/clear/xxx');
        $I->dontSee('{"success":true}');
    }
}