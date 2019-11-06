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

        $responses = $this->_getResponses();

        $server->setResponseOfPath('/v4/reports:batchGet', new ResponseStack(
            new Response($responses[0]),
            new Response($responses[1]),
            new Response($responses[2]),
            new Response($responses[3]),
            new Response($responses[4]),
            new Response($responses[5]),
            new Response($responses[6]),
            new Response($responses[7]),
            new Response($responses[8])
        ));

        $token = $I->getService('cmsService')->createSecurityToken();

        $I->login();

        $I->sendAjaxPostRequest('/cms/stats/update', ['token' => $token]);
        $I->see('{"success":true,"maxDate":null}');

        $visitData  = $I->getDbService()->queryRows("SELECT * FROM cms_analytics_day");
        $metricData = $I->getDbService()->queryRows("SELECT * FROM cms_analytics_metric");

        $expectedMetricData = [
            ['date' => '2019-10-29', 'type' => 'source', 'value' => 'afvallen-alkmaar.nl', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'os', 'value' => 'Macintosh', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'os', 'value' => 'Windows', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'os', 'value' => 'iOS', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'page', 'value' => '/en /t _blank', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'browser', 'value' => 'Chrome', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'location', 'value' => '(not set)', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'location', 'value' => 'Alkmaar', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'location', 'value' => 'Zuid-Scharwoude', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'resolutionDesktop', 'value' => '1536x864', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'resolutionDesktop', 'value' => '2560x1440', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'resolutionMobile', 'value' => '375x812', 'visits' => '1'],
            ['date' => '2019-10-30', 'type' => 'os', 'value' => 'Android', 'visits' => '1'],
            ['date' => '2019-10-30', 'type' => 'os', 'value' => 'Windows', 'visits' => '1'],
            ['date' => '2019-10-30', 'type' => 'page', 'value' => '/blog/lange-termijn-denken', 'visits' => '1'],
            ['date' => '2019-10-30', 'type' => 'browser', 'value' => 'Edge', 'visits' => '1'],
            ['date' => '2019-10-30', 'type' => 'browser', 'value' => 'Samsung Internet', 'visits' => '1'],
            ['date' => '2019-10-30', 'type' => 'location', 'value' => 'Haarlem', 'visits' => '1'],
            ['date' => '2019-10-30', 'type' => 'location', 'value' => 'Hilversum', 'visits' => '1'],
            ['date' => '2019-10-30', 'type' => 'resolutionDesktop', 'value' => '1440x900', 'visits' => '1'],
            ['date' => '2019-10-30', 'type' => 'resolutionDesktop', 'value' => '1600x900', 'visits' => '1'],
            ['date' => '2019-10-30', 'type' => 'resolutionDesktop', 'value' => '2560x1440', 'visits' => '1'],
            ['date' => '2019-10-30', 'type' => 'resolutionMobile', 'value' => '360x740', 'visits' => '1'],
            ['date' => '2019-10-29', 'type' => 'source', 'value' => '(direct)', 'visits' => '2'],
            ['date' => '2019-10-29', 'type' => 'page', 'value' => '/', 'visits' => '2'],
            ['date' => '2019-10-29', 'type' => 'browser', 'value' => 'Safari', 'visits' => '2'],
            ['date' => '2019-10-30', 'type' => 'source', 'value' => '(direct)', 'visits' => '2'],
            ['date' => '2019-10-30', 'type' => 'source', 'value' => 'google', 'visits' => '2'],
            ['date' => '2019-10-30', 'type' => 'os', 'value' => 'Macintosh', 'visits' => '2'],
            ['date' => '2019-10-30', 'type' => 'browser', 'value' => 'Safari', 'visits' => '2'],
            ['date' => '2019-10-30', 'type' => 'location', 'value' => 'Alkmaar', 'visits' => '2'],
            ['date' => '2019-10-30', 'type' => 'page', 'value' => '/', 'visits' => '3'],
        ];

        $expecteVisitData = [
            ['date' => "2019-10-29", 'visits' => 3, 'unique_visits' => 3],
            ['date' => "2019-10-30", 'visits' => 4, 'unique_visits' => 3]
        ];

        $I->assertEquals($expectedMetricData, $metricData);
        $I->assertEquals($expecteVisitData, $visitData);
    }

    /**
     * @return array
     */
    public function _getResponses(): array
    {
        return [
            '{"reports":[{"columnHeader":{"dimensions":["ga:year","ga:month","ga:day"],"metricHeader":{"metricHeaderEntries":[{"name":"visits","type":"INTEGER"},{"name":"unique","type":"PERCENT"}]}},"data":{"rows":[{"dimensions":["2019","10","29"],"metrics":[{"values":["3","100.0"]}]},{"dimensions":["2019","10","30"],"metrics":[{"values":["4","75.0"]}]}],"totals":[{"values":["7","85.71428571428571"]}],"rowCount":2,"minimums":[{"values":["3","75.0"]}],"maximums":[{"values":["4","100.0"]}]}}]}',
            '{"reports":[{"columnHeader":{"dimensions":["ga:year","ga:month","ga:day","ga:source"],"metricHeader":{"metricHeaderEntries":[{"name":"visits","type":"INTEGER"}]}},"data":{"rows":[{"dimensions":["2019","10","29","(direct)"],"metrics":[{"values":["2"]}]},{"dimensions":["2019","10","29","afvallen-alkmaar.nl"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","30","(direct)"],"metrics":[{"values":["2"]}]},{"dimensions":["2019","10","30","google"],"metrics":[{"values":["2"]}]}],"totals":[{"values":["7"]}],"rowCount":4,"minimums":[{"values":["1"]}],"maximums":[{"values":["2"]}]}}]}',
            '{"reports":[{"columnHeader":{"dimensions":["ga:year","ga:month","ga:day","ga:operatingSystem"],"metricHeader":{"metricHeaderEntries":[{"name":"visits","type":"INTEGER"}]}},"data":{"rows":[{"dimensions":["2019","10","29","iOS"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","29","Macintosh"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","29","Windows"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","30","Android"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","30","Macintosh"],"metrics":[{"values":["2"]}]},{"dimensions":["2019","10","30","Windows"],"metrics":[{"values":["1"]}]}],"totals":[{"values":["7"]}],"rowCount":6,"minimums":[{"values":["1"]}],"maximums":[{"values":["2"]}]}}]}',
            '{"reports":[{"columnHeader":{"dimensions":["ga:year","ga:month","ga:day","ga:pagePath"],"metricHeader":{"metricHeaderEntries":[{"name":"visits","type":"INTEGER"}]}},"data":{"rows":[{"dimensions":["2019","10","29","/"],"metrics":[{"values":["2"]}]},{"dimensions":["2019","10","29","/en /t _blank"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","30","/"],"metrics":[{"values":["3"]}]},{"dimensions":["2019","10","30","/blog/lange-termijn-denken"],"metrics":[{"values":["1"]}]}],"totals":[{"values":["7"]}],"rowCount":4,"minimums":[{"values":["1"]}],"maximums":[{"values":["3"]}]}}]}',
            '{"reports":[{"columnHeader":{"dimensions":["ga:year","ga:month","ga:day","ga:browser"],"metricHeader":{"metricHeaderEntries":[{"name":"visits","type":"INTEGER"}]}},"data":{"rows":[{"dimensions":["2019","10","29","Chrome"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","29","Safari"],"metrics":[{"values":["2"]}]},{"dimensions":["2019","10","30","Edge"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","30","Safari"],"metrics":[{"values":["2"]}]},{"dimensions":["2019","10","30","Samsung Internet"],"metrics":[{"values":["1"]}]}],"totals":[{"values":["7"]}],"rowCount":5,"minimums":[{"values":["1"]}],"maximums":[{"values":["2"]}]}}]}',
            '{"reports":[{"columnHeader":{"dimensions":["ga:year","ga:month","ga:day","ga:city"],"metricHeader":{"metricHeaderEntries":[{"name":"visits","type":"INTEGER"}]}},"data":{"rows":[{"dimensions":["2019","10","29","(not set)"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","29","Alkmaar"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","29","Zuid-Scharwoude"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","30","Alkmaar"],"metrics":[{"values":["2"]}]},{"dimensions":["2019","10","30","Haarlem"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","30","Hilversum"],"metrics":[{"values":["1"]}]}],"totals":[{"values":["7"]}],"rowCount":6,"minimums":[{"values":["1"]}],"maximums":[{"values":["2"]}]}}]}',
            '{"reports":[{"columnHeader":{"dimensions":["ga:year","ga:month","ga:day","ga:screenResolution"],"metricHeader":{"metricHeaderEntries":[{"name":"visits","type":"INTEGER"}]}},"data":{"rows":[{"dimensions":["2019","10","29","1536x864"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","29","2560x1440"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","30","1440x900"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","30","1600x900"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","30","2560x1440"],"metrics":[{"values":["1"]}]}],"totals":[{"values":["5"]}],"rowCount":5,"minimums":[{"values":["1"]}],"maximums":[{"values":["1"]}]}}]}',
            '{"reports":[{"columnHeader":{"dimensions":["ga:year","ga:month","ga:day","ga:screenResolution"],"metricHeader":{"metricHeaderEntries":[{"name":"visits","type":"INTEGER"}]}},"data":{"totals":[{"values":["0"]}]}}]}',
            '{"reports":[{"columnHeader":{"dimensions":["ga:year","ga:month","ga:day","ga:screenResolution"],"metricHeader":{"metricHeaderEntries":[{"name":"visits","type":"INTEGER"}]}},"data":{"rows":[{"dimensions":["2019","10","29","375x812"],"metrics":[{"values":["1"]}]},{"dimensions":["2019","10","30","360x740"],"metrics":[{"values":["1"]}]}],"totals":[{"values":["2"]}],"rowCount":2,"minimums":[{"values":["1"]}],"maximums":[{"values":["1"]}]}}]}',
        ];
    }
}