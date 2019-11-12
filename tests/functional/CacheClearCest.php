<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;

class CacheClearCest
{
    public function clearWorks(FunctionalTester $I)
    {
        $cacheFilePath = dirname(__DIR__) . '/TestSitePath/cache/cache/test:cacheTestKey';

        $I->getService('cache')->save('cacheTestKey', 'testValX', 5);

        $token = $I->getService('cmsService')->createSecurityToken();

        // invalid token, cache not cleared
        $I->amOnPage('/cache/clear/xxx');
        $I->dontSee('{"success":true}');

        $I->assertTrue(file_exists($cacheFilePath));

        // cache should be cleared now
        $I->amOnPage('/cache/clear/' . $token);
        $I->see('{"success":true}');

        $I->assertFalse(file_exists($cacheFilePath));
    }
}