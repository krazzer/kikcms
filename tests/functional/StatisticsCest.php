<?php
declare(strict_types=1);

namespace functional;


use FunctionalTester;

class StatisticsCest
{
    public function updateStatsWorks(FunctionalTester $I)
    {
        $token = $I->getService('cmsService')->createSecurityToken();

        $I->login();

        $I->sendAjaxPostRequest('/cms/stats/update', ['token' => $token]);
        $I->see('{"success":false,"maxDate":null}');
    }
}