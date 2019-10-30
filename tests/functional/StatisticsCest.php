<?php
declare(strict_types=1);

namespace functional;


use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseStack;
use FunctionalTester;

class StatisticsCest
{
    public function updateStatsWorks(FunctionalTester $I)
    {
        $server = new MockWebServer(8001);
        $server->start();

        $server->setResponseOfPath('/v4/reports:batchGet', new ResponseStack(
            new Response(json_encode(['count' => '1'])),
            new Response(json_encode(['count' => '2'])),
            new Response(json_encode(['count' => '3'])),
            new Response(json_encode(['count' => '4'])),
            new Response(json_encode(['count' => '5'])),
            new Response(json_encode(['count' => '6'])),
            new Response(json_encode(['count' => '7'])),
            new Response(json_encode(['count' => '8'])),
            new Response(json_encode(['count' => '9']))
        ));

        $token = $I->getService('cmsService')->createSecurityToken();

        $I->login();

        $I->sendAjaxPostRequest('/cms/stats/update', ['token' => $token]);
        $I->see('{"success":true,"maxDate":null}');
    }
}