<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;

class CacheClearCest
{
    public function clearWorks(FunctionalTester $I)
    {
        $I->getCache()->set('cacheTestKey', 'testValX', 5);

        $token = $I->getService('cmsService')->createSecurityToken();

        // invalid token, cache not cleared
        $I->amOnPage('/cache/clear/xxx');
        $I->dontSee('{"success":true}');

        $I->assertTrue($I->getCache()->has('cacheTestKey'));

        // cache should be cleared now
        $I->amOnPage('/cache/clear/' . $token);
        $I->see('{"success":true}');

        $I->assertFalse($I->getCache()->has('cacheTestKey'));
    }
}