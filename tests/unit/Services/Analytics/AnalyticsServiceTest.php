<?php
declare(strict_types=1);

namespace Services\Analytics;

use Codeception\Test\Unit;
use Helpers\TestHelper;
use KikCMS\Models\Analytics\GaDayVisit;
use KikCMS\Models\Analytics\GaVisitData;
use KikCMS\Services\Analytics\AnalyticsService;

class AnalyticsServiceTest extends Unit
{
    public function testGetOverviewData()
    {
        $di = (new TestHelper)->getTestDi();

        $analyticsService = new AnalyticsService();
        $analyticsService->setDI($di);

        $start = new \DateTime('2020-01-01');
        $end   = new \DateTime('2020-01-10');

        $analyticsService->dbService->truncate(GaDayVisit::class);

        // test empty db
        $result = $analyticsService->getOverviewData($start, $end);

        $expected = [
            'Aantal bezoeken'            => 0,
            'Aantal unieke bezoeken'     => 0,
            'Gemiddeld bezoek per dag'   => 0,
            'Gemiddeld bezoek per maand' => 0,
        ];

        $this->assertEquals($expected, $result);

        // test filled db
        $analyticsService->dbService->insertBulk(GaDayVisit::class, [
            ['date' => '2020-01-01', 'visits' => 2, 'unique_visits' => 1],
            ['date' => '2020-01-02', 'visits' => 2, 'unique_visits' => 1],
            ['date' => '2020-01-03', 'visits' => 2, 'unique_visits' => 1],
            ['date' => '2020-01-04', 'visits' => 2, 'unique_visits' => 1],
            ['date' => '2020-01-05', 'visits' => 2, 'unique_visits' => 1],
            ['date' => '2020-01-06', 'visits' => 2, 'unique_visits' => 1],
            ['date' => '2020-01-07', 'visits' => 2, 'unique_visits' => 1],
            ['date' => '2020-01-08', 'visits' => 2, 'unique_visits' => 1],
            ['date' => '2020-01-09', 'visits' => 2, 'unique_visits' => 1],
            ['date' => '2020-01-10', 'visits' => 2, 'unique_visits' => 1],
        ]);

        $result = $analyticsService->getOverviewData($start, $end);

        $expected = [
            'Aantal bezoeken'            => 20,
            'Aantal unieke bezoeken'     => 10,
            'Gemiddeld bezoek per dag'   => 2,
            'Gemiddeld bezoek per maand' => 61,
        ];

        $this->assertEquals($expected, $result);

        $analyticsService->dbService->truncate(GaDayVisit::class);
    }

    public function testGetVisitorData()
    {
        $di = (new TestHelper)->getTestDi();

        $analyticsService = new AnalyticsService();
        $analyticsService->setDI($di);

        $analyticsService->dbService->truncate(GaVisitData::class);
        $analyticsService->dbService->truncate(GaDayVisit::class);

        $start = new \DateTime('2013-02-01');
        $end   = new \DateTime('2013-03-01');

        // test empty db
        $expected = [];
        $result   = $analyticsService->getVisitorData($start, $end);
        $this->assertEquals($expected, $result);

        // test with data
        $this->insertVisitorTestData($analyticsService);

        $expected = [
            'os'       => [
                ['type' => 'os', 'value' => 'Macintosh', 'visits' => '3', 'percentage' => '30.0'],
                ['type' => 'os', 'value' => 'Windows', 'visits' => '2', 'percentage' => '20.0'],
                ['type' => 'os', 'value' => 'iOS', 'visits' => '1', 'percentage' => '10.0'],
            ],
            'source'   => [
                ['type' => 'source', 'value' => 'site-one.com', 'visits' => '4', 'percentage' => '40.0'],
                ['type' => 'source', 'value' => '(direct)', 'visits' => '3', 'percentage' => '30.0'],
                ['type' => 'source', 'value' => 'site-two.com', 'visits' => '2', 'percentage' => '20.0'],
                ['type' => 'source', 'value' => 'linkedin.com', 'visits' => '2', 'percentage' => '20.0'],
                ['type' => 'source', 'value' => 'site-three.com', 'visits' => '2', 'percentage' => '20.0'],
                ['type' => 'source', 'value' => 'site-four.com', 'visits' => '1', 'percentage' => '10.0'],
            ],
            'browser'  => [
                ['type' => 'browser', 'value' => 'Chrome', 'visits' => '6', 'percentage' => '60.0'],
                ['type' => 'browser', 'value' => 'Safari', 'visits' => '5', 'percentage' => '50.0'],
                ['type' => 'browser', 'value' => 'Internet Explorer', 'visits' => '1', 'percentage' => '10.0'],
                ['type' => 'browser', 'value' => 'Firefox', 'visits' => '1', 'percentage' => '10.0'],
            ],
            'location' => [
                ['type' => 'location', 'value' => 'Alkmaar', 'visits' => '4', 'percentage' => '40.0'],
                ['type' => 'location', 'value' => 'Ahmedabad', 'visits' => '2', 'percentage' => '20.0'],
                ['type' => 'location', 'value' => 'Groningen', 'visits' => '1', 'percentage' => '10.0'],
                ['type' => 'location', 'value' => '(not set)', 'visits' => '1', 'percentage' => '10.0'],
                ['type' => 'location', 'value' => 'Amsterdam', 'visits' => '1', 'percentage' => '10.0'],
                ['type' => 'location', 'value' => 'Duisburg', 'visits' => '1', 'percentage' => '10.0'],
                ['type' => 'location', 'value' => 'Gondomar', 'visits' => '1', 'percentage' => '10.0'],
                ['type' => 'location', 'value' => 'Ter Aar', 'visits' => '1', 'percentage' => '10.0'],
            ],
        ];

        $result = $analyticsService->getVisitorData($start, $end);

        $this->assertEquals($expected, $result);

        // test with limit
        $this->insertExtraVisitorTestData($analyticsService);

        $result = $analyticsService->getVisitorData($start, $end);

        $this->assertCount(25, $result['location']);

        $analyticsService->dbService->truncate(GaVisitData::class);
        $analyticsService->dbService->truncate(GaDayVisit::class);
    }

    /**
     * @param AnalyticsService $analyticsService
     */
    private function insertVisitorTestData(AnalyticsService $analyticsService)
    {
        $analyticsService->dbService->insertBulk(GaDayVisit::class, [
            ["date" => "2013-2-11", "visits" => 1, "unique_visits" => 1],
            ["date" => "2013-2-12", "visits" => 1, "unique_visits" => 1],
            ["date" => "2013-2-16", "visits" => 1, "unique_visits" => 1],
            ["date" => "2013-2-18", "visits" => 1, "unique_visits" => 1],
            ["date" => "2013-2-19", "visits" => 1, "unique_visits" => 1],
            ["date" => "2013-2-20", "visits" => 1, "unique_visits" => 1],
            ["date" => "2013-2-23", "visits" => 1, "unique_visits" => 1],
            ["date" => "2013-2-24", "visits" => 1, "unique_visits" => 1],
            ["date" => "2013-2-26", "visits" => 1, "unique_visits" => 1],
            ["date" => "2013-2-27", "visits" => 1, "unique_visits" => 1],
        ]);

        $analyticsService->dbService->insertBulk(GaVisitData::class, [
            ["date" => "2013-2-12", "type" => "source", "value" => "site-three.com", "visits" => "1"],
            ["date" => "2013-2-12", "type" => "os", "value" => "Windows", "visits" => "1"],
            ["date" => "2013-2-12", "type" => "browser", "value" => "Chrome", "visits" => "1"],
            ["date" => "2013-2-12", "type" => "browser", "value" => "Firefox", "visits" => "1"],
            ["date" => "2013-2-12", "type" => "location", "value" => "Gondomar", "visits" => "1"],
            ["date" => "2013-2-16", "type" => "source", "value" => "site-one.com", "visits" => "1"],
            ["date" => "2013-2-16", "type" => "os", "value" => "Macintosh", "visits" => "1"],
            ["date" => "2013-2-16", "type" => "browser", "value" => "Safari", "visits" => "1"],
            ["date" => "2013-2-16", "type" => "location", "value" => "Alkmaar", "visits" => "1"],
            ["date" => "2013-2-18", "type" => "source", "value" => "site-two.com", "visits" => "1"],
            ["date" => "2013-2-18", "type" => "source", "value" => "linkedin.com", "visits" => "1"],
            ["date" => "2013-2-18", "type" => "browser", "value" => "Chrome", "visits" => "1"],
            ["date" => "2013-2-18", "type" => "browser", "value" => "Safari", "visits" => "1"],
            ["date" => "2013-2-18", "type" => "location", "value" => "Duisburg", "visits" => "1"],
            ["date" => "2013-2-18", "type" => "location", "value" => "Ter Aar", "visits" => "1"],
            ["date" => "2013-2-19", "type" => "source", "value" => "(direct)", "visits" => "1"],
            ["date" => "2013-2-19", "type" => "source", "value" => "site-three.com", "visits" => "1"],
            ["date" => "2013-2-19", "type" => "browser", "value" => "Chrome", "visits" => "1"],
            ["date" => "2013-2-19", "type" => "browser", "value" => "Safari", "visits" => "1"],
            ["date" => "2013-2-20", "type" => "source", "value" => "(direct)", "visits" => "1"],
            ["date" => "2013-2-20", "type" => "source", "value" => "site-one.com", "visits" => "1"],
            ["date" => "2013-2-20", "type" => "os", "value" => "Macintosh", "visits" => "1"],
            ["date" => "2013-2-20", "type" => "os", "value" => "Windows", "visits" => "1"],
            ["date" => "2013-2-20", "type" => "browser", "value" => "Chrome", "visits" => "1"],
            ["date" => "2013-2-20", "type" => "browser", "value" => "Safari", "visits" => "1"],
            ["date" => "2013-2-20", "type" => "location", "value" => "Alkmaar", "visits" => "1"],
            ["date" => "2013-2-20", "type" => "location", "value" => "Amsterdam", "visits" => "1"],
            ["date" => "2013-2-23", "type" => "source", "value" => "site-one.com", "visits" => "1"],
            ["date" => "2013-2-23", "type" => "os", "value" => "Macintosh", "visits" => "1"],
            ["date" => "2013-2-23", "type" => "browser", "value" => "Safari", "visits" => "1"],
            ["date" => "2013-2-23", "type" => "location", "value" => "Alkmaar", "visits" => "1"],
            ["date" => "2013-2-24", "type" => "source", "value" => "(direct)", "visits" => "1"],
            ["date" => "2013-2-24", "type" => "os", "value" => "iOS", "visits" => "1"],
            ["date" => "2013-2-24", "type" => "browser", "value" => "Chrome", "visits" => "1"],
            ["date" => "2013-2-24", "type" => "location", "value" => "Alkmaar", "visits" => "1"],
            ["date" => "2013-2-26", "type" => "source", "value" => "site-one.com", "visits" => "1"],
            ["date" => "2013-2-26", "type" => "source", "value" => "site-four.com", "visits" => "1"],
            ["date" => "2013-2-27", "type" => "location", "value" => "(not set)", "visits" => "1"],
            ["date" => "2013-2-27", "type" => "location", "value" => "Ahmedabad", "visits" => "1"],
            ["date" => "2013-2-28", "type" => "source", "value" => "site-two.com", "visits" => "1"],
            ["date" => "2013-2-28", "type" => "source", "value" => "linkedin.com", "visits" => "1"],
            ["date" => "2013-2-28", "type" => "browser", "value" => "Chrome", "visits" => "1"],
            ["date" => "2013-2-28", "type" => "browser", "value" => "Internet Explorer", "visits" => "1"],
            ["date" => "2013-2-28", "type" => "location", "value" => "Ahmedabad", "visits" => "1"],
            ["date" => "2013-2-28", "type" => "location", "value" => "Groningen", "visits" => "1"],
        ]);
    }

    /**
     * @param AnalyticsService $analyticsService
     */
    private function insertExtraVisitorTestData(AnalyticsService $analyticsService)
    {
        $visitorDataInsert = [];

        for($i=1; $i<=50; $i++){
            $visitorDataInsert[] = ["date" => "2013-2-10", "type" => "location", "value" => "L" . $i, "visits" => 1];
        }

        $analyticsService->dbService->insertBulk(GaVisitData::class, $visitorDataInsert);
    }
}
