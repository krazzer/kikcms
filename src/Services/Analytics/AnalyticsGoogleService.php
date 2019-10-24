<?php
declare(strict_types=1);

namespace KikCMS\Services\Analytics;


use DateTime;
use Google_Service_AnalyticsReporting_ColumnHeader;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_DateRangeValues;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_MetricHeaderEntry;
use Google_Service_AnalyticsReporting_Report;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_ReportRow;
use KikCMS\Config\StatisticsConfig;
use Phalcon\Config;
use Phalcon\Di\Injectable;

/**
 * @property Config $config
 */
class AnalyticsGoogleService extends Injectable
{
    /**
     * @return array
     */
    public function getVisitData(): array
    {
        return $this->getVisitorData(null, null, ["ga:percentNewSessions" => "unique"]);
    }

    /**
     * @param string $dimensionName
     * @param DateTime|null $fromDate
     * @param array $addMetrics
     * @param array $filters
     *
     * @return array
     */
    public function getVisitorData(string $dimensionName = null, DateTime $fromDate = null, array $addMetrics = [], array $filters = []): array
    {
        $fromDate = $fromDate ?: new DateTime('2005-01-01');

        $viewId = (string) $this->config->analytics->viewId;

        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($fromDate->format('Y-m-d'));
        $dateRange->setEndDate("today");

        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:visits");
        $sessions->setAlias("visits");

        $metrics = [$sessions];

        foreach ($addMetrics as $metricName => $alias) {
            $metric = new Google_Service_AnalyticsReporting_Metric();
            $metric->setExpression($metricName);
            $metric->setAlias($alias);

            $metrics[] = $metric;
        }

        $year = new Google_Service_AnalyticsReporting_Dimension();
        $year->setName("ga:year");

        $month = new Google_Service_AnalyticsReporting_Dimension();
        $month->setName("ga:month");

        $day = new Google_Service_AnalyticsReporting_Dimension();
        $day->setName("ga:day");

        $dimensions = [$year, $month, $day];

        if ($dimensionName) {
            $dimension = new Google_Service_AnalyticsReporting_Dimension();
            $dimension->setName($dimensionName);

            $dimensions[] = $dimension;
        }

        // Create the ReportRequest object.
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics($metrics);
        $request->setDimensions($dimensions);
        $request->setPageSize(StatisticsConfig::MAX_IMPORT_ROWS);

        if ($filters) {
            foreach ($filters as $name => $value) {
                $request->setFiltersExpression($name . '==' . $value);
            }
        }

        return $this->reportRequestToArray($request);
    }

    /**
     * Request the data from the given google request and convert it to an array
     *
     * @param Google_Service_AnalyticsReporting_ReportRequest $request
     * @return array
     */
    private function reportRequestToArray(Google_Service_AnalyticsReporting_ReportRequest $request): array
    {
        $results = [];

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests(array($request));
        $reports = $this->analytics->reports->batchGet($body);

        for ($reportIndex = 0; $reportIndex < count($reports); $reportIndex++) {
            /** @var Google_Service_AnalyticsReporting_Report $report */
            $report = $reports[$reportIndex];

            /** @var Google_Service_AnalyticsReporting_ColumnHeader $header */
            $header           = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders    = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows             = $report->getData()->getRows();

            for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
                $results[] = $this->reportRowToArray($rows[$rowIndex], $metricHeaders, $dimensionHeaders);
            }
        }

        return $results;
    }

    /**
     * @param Google_Service_AnalyticsReporting_ReportRow $reportRow
     * @param Google_Service_AnalyticsReporting_MetricHeaderEntry $metricHeaders
     * @param array $dimensionHeaders
     * @return array
     */
    private function reportRowToArray($reportRow, $metricHeaders, array $dimensionHeaders): array
    {
        $resultRow = [];

        $dimensions = $reportRow->getDimensions();
        $metrics    = $reportRow->getMetrics();

        for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
            $resultRow[$dimensionHeaders[$i]] = $dimensions[$i];
        }

        for ($j = 0; $j < count($metrics); $j++) {
            /** @var Google_Service_AnalyticsReporting_DateRangeValues $metric */
            $metric = $metrics[$j];
            $values = $metric->getValues();
            for ($k = 0; $k < count($values); $k++) {
                /** @var Google_Service_AnalyticsReporting_MetricHeaderEntry $entry */
                $entry                        = $metricHeaders[$k];
                $resultRow[$entry->getName()] = $values[$k];
            }
        }

        return $resultRow;
    }
}