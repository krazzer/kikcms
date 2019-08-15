<?php
declare(strict_types=1);

namespace Services\Analytics;

use Codeception\Test\Unit;
use Helpers\TestHelper;
use KikCMS\Models\Analytics\GaDayVisit;
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
}
