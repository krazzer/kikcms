<?php
declare(strict_types=1);

namespace functional;


use DateInterval;
use DateTime;
use FunctionalTester;
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DimensionHeader;
use Google\Analytics\Data\V1beta\DimensionValue;
use Google\Analytics\Data\V1beta\MetricHeader;
use Google\Analytics\Data\V1beta\MetricType;
use Google\Analytics\Data\V1beta\MetricValue;
use Google\Analytics\Data\V1beta\Row;
use Google\Analytics\Data\V1beta\RunReportResponse;
use KikCMS\Models\Analytics\GaDayVisit;
use Mockery;

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
                'unique_visits' => $i % 2 ? 1 : 0,
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
        list($response, $responseMetric) = $this->_getResponses();

        $myMock = Mockery::mock(BetaAnalyticsDataClient::class);

        $myMock->shouldReceive('runReport')->andReturnValues([$response, $responseMetric, $responseMetric,
            $responseMetric, $responseMetric, $responseMetric, $responseMetric]);

        $I->getApplication()->di->set('analyticsData', $myMock);

        $token = $I->getService('cmsService')->createSecurityToken();

        $I->sendAjaxPostRequest('/cms/stats/update', ['token' => $token]);

        $I->see('{"success":true,"maxDate":null}');

        $visitData  = $I->getDbService()->queryRows("SELECT * FROM cms_analytics_day");
        $metricData = $I->getDbService()->queryRows("SELECT * FROM cms_analytics_metric");

        $expectedMetricData = [
            ['date' => '2020-01-01', 'type' => 'source', 'value' => 'value1', 'visits' => '1'],
            ['date' => '2020-01-01', 'type' => 'os', 'value' => 'value1', 'visits' => '1'],
            ['date' => '2020-01-01', 'type' => 'page', 'value' => 'value1', 'visits' => '1'],
            ['date' => '2020-01-01', 'type' => 'browser', 'value' => 'value1', 'visits' => '1'],
            ['date' => '2020-01-01', 'type' => 'location', 'value' => 'value1', 'visits' => '1'],
            ['date' => '2020-01-01', 'type' => 'resolutionDesktop', 'value' => 'value1', 'visits' => '1'],
        ];

        $expecteVisitData = [
            ['date' => "2020-01-01", 'visits' => 1, 'unique_visits' => 2],
        ];

        $I->assertEquals($expectedMetricData, $metricData);
        $I->assertEquals($expecteVisitData, $visitData);
    }

    /**
     * @see https://cloud.google.com/php/docs/reference/analytics-data/latest/V1beta.RunReportResponse
     * for how to build a RunReportResponse
     *
     * @return array
     */
    public function _getResponses(): array
    {
        return [
            new RunReportResponse([
                'dimension_headers' => [
                    new DimensionHeader(['name' => 'date'])
                ],

                'metric_headers' => [
                    new MetricHeader(["name" => "sessions", "type" => MetricType::TYPE_INTEGER]),
                    new MetricHeader(["name" => "activeUsers", "type" => MetricType::TYPE_INTEGER]),
                ],

                'rows' => [
                    new Row([
                        'dimension_values' => [
                            new DimensionValue(['value' => '20200101']),
                        ],
                        'metric_values'    => [
                            new MetricValue(['value' => 1]),
                            new MetricValue(['value' => 2]),
                        ],
                    ])
                ]
            ]),

            new RunReportResponse([
                'dimension_headers' => [
                    new DimensionHeader(['name' => 'date']),
                    new DimensionHeader(['name' => 'screenResolution']),
                    new DimensionHeader(['name' => 'deviceCategory']),
                ],

                'metric_headers' => [
                    new MetricHeader(["name" => "sessions", "type" => MetricType::TYPE_INTEGER]),
                    new MetricHeader(["name" => "activeUsers", "type" => MetricType::TYPE_INTEGER]),
                ],

                'rows' => [
                    new Row([
                        'dimension_values' => [
                            new DimensionValue(['value' => '20200101']),
                            new DimensionValue(['value' => 'value1']),
                            new DimensionValue(['value' => 'desktop']),
                        ],
                        'metric_values'    => [
                            new MetricValue(['value' => 1]),
                            new MetricValue(['value' => 2]),
                        ],
                    ])
                ]
            ])
        ];
    }
}