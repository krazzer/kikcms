<?php

namespace KikCMS\Services\Analytics;

use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy;
use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\GaConfig;
use KikCMS\Config\StatisticsConfig;
use KikCMS\Models\Analytics\GaDayVisit;
use KikCMS\Models\Analytics\GaVisitData;

/**
 * Service for handling the analytics v4 using the new (2022) anaylitcs data API
 */
class AnalyticsDataService extends Injectable
{
    /**
     * @return array
     */
    public function getVisitData(): array
    {
        $response = $this->analyticsData->runReport([
            'property'   => 'properties/' . $this->config->analytics->propertyId,
            'dateRanges' => [
                new DateRange(['start_date' => GaConfig::GA4_LAUNCH_DATE, 'end_date' => 'today'])
            ],
            'dimensions' => [
                new Dimension(['name' => 'date']),
            ],
            'metrics'    => [
                new Metric(['name' => 'sessions']),
                new Metric(['name' => 'activeUsers']),
            ],
            'orderBys'   => [
                new OrderBy(['dimension' => new DimensionOrderBy(['dimension_name' => 'date'])])
            ],
        ]);

        $results = [];

        foreach ($response->getRows() as $row) {
            $sessions = $row->getMetricValues()[0]->getValue();
            $users    = $row->getMetricValues()[1]->getValue();

            $results[] = [
                GaDayVisit::FIELD_DATE          => $row->getDimensionValues()[0]->getValue(),
                GaDayVisit::FIELD_VISITS        => $sessions,
                GaDayVisit::FIELD_UNIQUE_VISITS => min($users, $sessions),
            ];
        }

        return $results;
    }

    /**
     * @param string $dimension
     * @param string $metric
     * @param string|null $subDimension
     * @return array
     */
    public function getMetricData(string $dimension, string $metric, string $subDimension = null): array
    {
        $dimensions = [
            new Dimension(['name' => 'date']),
            new Dimension(['name' => $dimension]),
        ];

        if ($subDimension) {
            $dimensions[] = new Dimension(['name' => 'deviceCategory']);
        }

        $lastUpdate = $this->analyticsService->getMaxMetricDate($metric);

        $response = $this->analyticsData->runReport([
            'property'   => 'properties/' . $this->config->analytics->propertyId,
            'dimensions' => $dimensions,
            'dateRanges' => [new DateRange(['start_date' => $lastUpdate->format('Y-m-d'), 'end_date' => 'today'])],
            'metrics'    => [new Metric(['name' => 'sessions']), new Metric(['name' => 'activeUsers'])],
            'orderBys'   => [new OrderBy(['dimension' => new DimensionOrderBy(['dimension_name' => 'date'])])],
        ]);

        $results = [];

        foreach ($response->getRows() as $row) {
            $type = $metric;

            if ($subDimension) {
                $type .= ucfirst($row->getDimensionValues()[2]->getValue());
            }

            $value = $row->getDimensionValues()[1]->getValue();

            // empty value in path is same as /, so replace to merge
            if ($metric === GaConfig::METRIC_PATH && $value === '') {
                $value = '/';
            }

            if ( ! array_key_exists($type, StatisticsConfig::GA_TYPES)) {
                continue;
            }

            $results[] = [
                GaVisitData::FIELD_DATE   => $row->getDimensionValues()[0]->getValue(),
                GaVisitData::FIELD_TYPE   => $type,
                GaVisitData::FIELD_VALUE  => $value,
                GaVisitData::FIELD_VISITS => $row->getMetricValues()[0]->getValue(),
            ];
        }

        return $results;
    }

    /**
     * @return array
     */
    public function getVisitMetricData(): array
    {
        $cat = GaConfig::DIMENSION_DEVICECATEGORY;

        $resSource     = $this->getMetricData(GaConfig::DIMENSION_SOURCE, GaConfig::METRIC_SOURCE);
        $resOs         = $this->getMetricData(GaConfig::DIMENSION_OS, GaConfig::METRIC_OS);
        $resPath       = $this->getMetricData(GaConfig::DIMENSION_PATH, GaConfig::METRIC_PATH);
        $resBrowser    = $this->getMetricData(GaConfig::DIMENSION_BROWSER, GaConfig::METRIC_BROWSER);
        $resCountry    = $this->getMetricData(GaConfig::DIMENSION_COUNTRY, GaConfig::METRIC_COUNTRY);
        $resResolution = $this->getMetricData(GaConfig::DIMENSION_RESOLUTION, GaConfig::METRIC_RESOLUTION, $cat);

        return array_merge($resSource, $resOs, $resPath, $resBrowser, $resCountry, $resResolution);
    }
}