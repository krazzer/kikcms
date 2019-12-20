<?php
declare(strict_types=1);

namespace functional;


use DateInterval;
use DateTime;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseStack;
use FunctionalTester;
use KikCMS\Models\Analytics\GaDayVisit;

class StatisticsCest
{
    public function _before(FunctionalTester $I)
    {
        $I->login();
    }

    public function getVisitorsWorks(FunctionalTester $I)
    {
        $currentDate = new DateTime('2019-01-01');

        $insertData = [];

        for ($i = 0; $i < 100; $i++) {
            $currentDate = $currentDate->add(new DateInterval('P1D'));

            $insertData[] = [
                'date'          => $currentDate->format('Y-m-d'),
                'visits'        => 1,
                'unique_visits' => $i%2 ? 1 : 0,
            ];
        }

        $I->getDbService()->insertBulk(GaDayVisit::class, $insertData);

        $I->sendAjaxPostRequest('/cms/stats/getVisitors');
        $I->see('{"visitorsData":{"cols":[{"label":"","type":"string"},{"label":"Visitors","type":"number"},{"label":"Unique visitors","type":"number"}],"rows":[{"c":[{"v":"Jan 2019"},{"v":"30"},{"v":"15"}]},{"c":[{"v":"Feb 2019"},{"v":"28"},{"v":"14"}]},{"c":[{"v":"Mar 2019"},{"v":"31"},{"v":"15"}]},{"c":[{"v":"Apr 2019"},{"v":"11"},{"v":"6"}]}]},"visitorData":[],"overviewData":{"Total visitors":100,"Total unique visitors":50,"Average visitors per day":1,"Average visitors per month":30},"requiresUpdate":true}');

        $I->sendAjaxPostRequest('/cms/stats/getVisitors', ['interval' => 'daily']);
        $I->see('{"visitorsData":{"cols":[{"label":"","type":"string"},{"label":"Visitors","type":"number"},{"label":"Unique visitors","type":"number"}],"rows":[{"c":[{"v":"Jan  2 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan  3 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan  4 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan  5 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan  6 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan  7 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan  8 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan  9 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan 10 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan 11 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan 12 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan 13 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan 14 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan 15 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan 16 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan 17 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan 18 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan 19 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan 20 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan 21 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan 22 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan 23 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan 24 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan 25 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan 26 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan 27 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan 28 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan 29 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Jan 30 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Jan 31 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb  1 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb  2 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb  3 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb  4 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb  5 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb  6 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb  7 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb  8 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb  9 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb 10 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb 11 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb 12 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb 13 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb 14 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb 15 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb 16 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb 17 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb 18 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb 19 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb 20 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb 21 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb 22 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb 23 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb 24 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb 25 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb 26 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Feb 27 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Feb 28 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar  1 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar  2 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar  3 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar  4 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar  5 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar  6 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar  7 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar  8 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar  9 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar 10 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar 11 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar 12 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar 13 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar 14 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar 15 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar 16 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar 17 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar 18 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar 19 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar 20 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar 21 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar 22 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar 23 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar 24 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar 25 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar 26 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar 27 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar 28 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar 29 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Mar 30 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Mar 31 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Apr  1 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Apr  2 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Apr  3 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Apr  4 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Apr  5 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Apr  6 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Apr  7 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Apr  8 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Apr  9 2019"},{"v":"1"},{"v":"1"}]},{"c":[{"v":"Apr 10 2019"},{"v":"1"},{"v":"0"}]},{"c":[{"v":"Apr 11 2019"},{"v":"1"},{"v":"1"}]}]},"visitorData":[],"overviewData":{"Total visitors":100,"Total unique visitors":50,"Average visitors per day":1,"Average visitors per month":30},"requiresUpdate":true}');
    }

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