<?php
declare(strict_types=1);

namespace functional;


use FunctionalTester;

class CacheTaskCest
{
    public function clearActionWorks(FunctionalTester $I)
    {
        $I->runShellCommand('php /opt/project/tests/TestSitePath/kikcms cache clear');
    }
}