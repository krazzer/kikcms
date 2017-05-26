<?php

namespace KikCMS\Services\Analytics;


use DateTime;
use KikCMS\Classes\DbService;
use KikCMS\Config\DbConfig;
use KikCMS\Config\StatisticsConfig;
use KikCMS\Models\Analytics\GaDayVisit;
use Monolog\Logger;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property \Google_Service_AnalyticsReporting $analytics
 * @property DbService $dbService
 */
class AnalyticsService extends Injectable
{
    /**
     * Fetches statics from Google, save them in the DB
     * @return bool
     */
    public function importIntoDb(): bool
    {
        $this->db->begin();

        try {
            $results = $this->getVisitDataFromGoogle();

            $insertValues = [];

            foreach ($results as $resultRow) {
                $date   = $resultRow['ga:year'] . '-' . $resultRow['ga:month'] . '-' . $resultRow['ga:day'];
                $visits = (int) $resultRow['visits'];
                $unique = (int) $visits * ($resultRow['unique'] / 100);

                $insertValues[] = "('" . $date . "', " . $visits . ", " . $unique . ")";
            }

            $this->db->query("DELETE FROM " . GaDayVisit::TABLE);

            $this->db->query("
                INSERT INTO ga_day_visit (`date`, visits, unique_visits) 
                VALUES " . implode(',', $insertValues) . "
            ");
        } catch (\Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception);
            $this->db->rollback();
            return false;
        }

        return $this->db->commit();
    }

    /**
     * @return DateTime|null
     */
    public function getMaxDate()
    {
        $query   = (new Builder())->from(GaDayVisit::class)->columns(['MAX(date)']);
        $maxDate = $this->dbService->getValue($query);

        $date = DateTime::createFromFormat(DbConfig::SQL_DATE_FORMAT, $maxDate);

        if ( ! $date) {
            return null;
        }

        return $date;
    }

    /**
     * @return DateTime|null
     */
    public function getMinDate()
    {
        $query   = (new Builder())->from(GaDayVisit::class)->columns(['MIN(date)']);
        $minDate = $this->dbService->getValue($query);

        $date = DateTime::createFromFormat(DbConfig::SQL_DATE_FORMAT, $minDate);

        if ( ! $date) {
            return null;
        }

        return $date;
    }

    /**
     * @param string $interval
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return array
     */
    public function getVisitorsChartData(string $interval, DateTime $start = null, DateTime $end = null): array
    {
        $dateDisplayFormat  = $this->translator->tl('system.dateDisplayFormat');
        $monthDisplayFormat = $this->translator->tl('system.monthDisplayFormat');

        $query = $this->getChartQuery($start, $end);

        if ($interval == StatisticsConfig::VISITS_DAILY) {
            $rows = $this->getChartQueryResult($query, $dateDisplayFormat);
        } else {
            $query
                ->columns(array_merge($query->getColumns(), ["DATE_FORMAT(date, '%Y%m') AS month"]))
                ->groupBy('month');

            $rows = $this->getChartQueryResult($query, $monthDisplayFormat);
        }

        $strVisitors       = $this->translator->tl('statistics.visitors');
        $strUniqueVisitors = $this->translator->tl('statistics.uniqueVisitors');

        $cols = [
            ["label" => "", "type" => "string"],
            ["label" => $strVisitors, "type" => "number"],
            ["label" => $strUniqueVisitors, "type" => "number"],
        ];

        return [
            'cols' => $cols,
            'rows' => $rows,
        ];
    }

    /**
     * Checks if the db is up to date
     *
     * @return bool
     */
    public function needsUpdate(): bool
    {
        $maxDate = $this->getMaxDate();

        if ( ! $maxDate) {
            return true;
        }

        return $maxDate->format('dmY') !== (new DateTime())->format('dmY');
    }

    /**
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return Builder
     */
    private function getChartQuery(DateTime $start = null, DateTime $end = null): Builder
    {
        $query = (new Builder())
            ->from(GaDayVisit::class)
            ->columns(['date', 'SUM(visits) AS visits', 'SUM(unique_visits) AS unique_visits'])
            ->groupBy('date');

        if ($start) {
            $query->andWhere('date >= :dateStart:', [
                'dateStart' => $start->format(DbConfig::SQL_DATE_FORMAT)
            ]);
        }

        if ($end) {
            $query->andWhere('date <= :dateEnd:', [
                'dateEnd' => $end->format(DbConfig::SQL_DATE_FORMAT)
            ]);
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @param string $dateFormat
     * @return array
     */
    private function getChartQueryResult(Builder $query, string $dateFormat): array
    {
        $rows   = [];
        $visits = $query->getQuery()->execute()->toArray();

        foreach ($visits as $visit) {
            $rows[] = ['c' => [
                ["v" => strftime($dateFormat, strtotime($visit['date']))],
                ["v" => $visit['visits']],
                ["v" => $visit['unique_visits']]
            ]];
        }

        return $rows;
    }

    /**
     * @return array
     */
    private function getVisitDataFromGoogle(): array
    {
        $viewId = (string) $this->config->analytics->viewId;

        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("2005-01-01");
        $dateRange->setEndDate("today");

        $sessions = new \Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:visits");
        $sessions->setAlias("visits");

        $sessionsUnique = new \Google_Service_AnalyticsReporting_Metric();
        $sessionsUnique->setExpression("ga:percentNewSessions");
        $sessionsUnique->setAlias("unique");

        //Create the Dimensions object.
        $year = new \Google_Service_AnalyticsReporting_Dimension();
        $year->setName("ga:year");

        $month = new \Google_Service_AnalyticsReporting_Dimension();
        $month->setName("ga:month");

        $day = new \Google_Service_AnalyticsReporting_Dimension();
        $day->setName("ga:day");

        // Create the ReportRequest object.
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics([$sessions, $sessionsUnique]);
        $request->setDimensions([$year, $month, $day]);

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests(array($request));
        $reports = $this->analytics->reports->batchGet($body);

        $results = [];

        for ($reportIndex = 0; $reportIndex < count($reports); $reportIndex++) {
            $report = $reports[$reportIndex];

            /** @var \Google_Service_AnalyticsReporting_ColumnHeader $header */
            $header           = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders    = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows             = $report->getData()->getRows();

            for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
                $resultRow = [];

                /** @var \Google_Service_AnalyticsReporting_ReportRow $row */
                $row        = $rows[$rowIndex];
                $dimensions = $row->getDimensions();
                $metrics    = $row->getMetrics();

                for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
                    $resultRow[$dimensionHeaders[$i]] = $dimensions[$i];
                }

                for ($j = 0; $j < count($metrics); $j++) {
                    /** @var \Google_Service_AnalyticsReporting_DateRangeValues $metric */
                    $metric = $metrics[$j];
                    $values = $metric->getValues();
                    for ($k = 0; $k < count($values); $k++) {
                        /** @var \Google_Service_AnalyticsReporting_MetricHeaderEntry $entry */
                        $entry                        = $metricHeaders[$k];
                        $resultRow[$entry->getName()] = $values[$k];
                    }
                }

                $results[] = $resultRow;
            }
        }

        return $results;
    }
}