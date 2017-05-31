<?php

namespace KikCMS\Services\Analytics;


use DateTime;
use KikCMS\Classes\DbService;
use KikCMS\Config\DbConfig;
use KikCMS\Config\StatisticsConfig;
use KikCMS\Models\Analytics\GaDayVisit;
use KikCMS\Models\Analytics\GaVisitData;
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

            $this->importVisitorData();

            $results = array_map(function ($row) {
                return [
                    GaDayVisit::FIELD_DATE          => $row['ga:year'] . '-' . $row['ga:month'] . '-' . $row['ga:day'],
                    GaDayVisit::FIELD_VISITS        => (int) $row['visits'],
                    GaDayVisit::FIELD_UNIQUE_VISITS => (int) $row['visits'] * ($row['unique'] / 100),
                ];
            }, $results);

            $this->dbService->truncate(GaDayVisit::class);
            $this->dbService->insertBulk(GaDayVisit::class, $results);
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
        $query = (new Builder())->from(GaDayVisit::class)->columns(['MAX(date)']);
        return $this->dbService->getDate($query);
    }

    /**
     * @return DateTime|null
     */
    public function getMinDate()
    {
        $query = (new Builder())->from(GaDayVisit::class)->columns(['MIN(date)']);
        return $this->dbService->getDate($query);
    }

    /**
     * @param DateTime|null $start
     * @param DateTime|null $end
     * @return array
     */
    public function getOverviewData(DateTime $start = null, DateTime $end = null): array
    {
        $totalVisits       = $this->getTotalVisits($start, $end);
        $totalUniqueVisits = $this->getTotalUniqueVisits($start, $end);
        $dailyAverage      = $this->getDailyAverage($start, $end);
        $monthlyAverage    = $this->getMonthlyAverage($start, $end);

        return [
            $this->translator->tl('statistics.overview.totalVisits')       => $totalVisits,
            $this->translator->tl('statistics.overview.totalUniqueVisits') => $totalUniqueVisits,
            $this->translator->tl('statistics.overview.dailyAverage')      => $dailyAverage,
            $this->translator->tl('statistics.overview.monthlyAverage')    => $monthlyAverage,
        ];
    }

    /**
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return array
     */
    public function getVisitorData(DateTime $start = null, DateTime $end = null): array
    {
        $totalVisits = $this->getTotalVisits($start, $end);
        $visitorData = [];

        $query = (new Builder)
            ->from(GaVisitData::class)
            ->columns([
                GaVisitData::FIELD_TYPE,
                GaVisitData::FIELD_VALUE,
                'SUM(' . GaVisitData::FIELD_VISITS . ') AS visits',
                'ROUND((SUM(' . GaVisitData::FIELD_VISITS . ') / ' . $totalVisits . ') * 100, 1) AS percentage'
            ])
            ->groupBy(GaVisitData::FIELD_VALUE)
            ->orderBy('visits DESC')
            ->limit(count(StatisticsConfig::TYPES) * 50);

        $this->addDateWhere($query, $start, $end);

        $results = $query->getQuery()->execute()->toArray();

        foreach ($results as $result){
            $type = $result[GaVisitData::FIELD_TYPE];

            if( ! array_key_exists($type, $visitorData)){
                $visitorData[$type] = [];
            }

            if(count($visitorData[$type]) >= 25){
                continue;
            }

            $visitorData[$type][] = $result;
        }

        return $visitorData;
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
    public function requiresUpdate(): bool
    {
        $maxDate = $this->getMaxDate();

        if ( ! $maxDate || $maxDate->format('dmY') !== (new DateTime())->format('dmY')) {
            return true;
        }

        $typeMaxDates = $this->getMaxDatePerVisitDataType();

        foreach ($typeMaxDates as $type => $maxDate){
            if ( ! $maxDate || $maxDate->format('dmY') !== (new DateTime())->format('dmY')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Builder $query
     * @param DateTime|null $start
     * @param DateTime|null $end
     */
    private function addDateWhere(Builder $query, DateTime $start = null, DateTime $end = null)
    {
        if ($start) {
            $query->andWhere(GaDayVisit::FIELD_DATE . ' >= :dateStart:', [
                'dateStart' => $start->format(DbConfig::SQL_DATE_FORMAT)
            ]);
        }

        if ($end) {
            $query->andWhere(GaDayVisit::FIELD_DATE . ' <= :dateEnd:', [
                'dateEnd' => $end->format(DbConfig::SQL_DATE_FORMAT)
            ]);
        }
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

        $this->addDateWhere($query, $start, $end);

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
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return int
     */
    private function getDailyAverage(DateTime $start = null, DateTime $end = null): int
    {
        $query = (new Builder())
            ->from(GaDayVisit::class)
            ->columns(['AVG(' . GaDayVisit::FIELD_VISITS . ')']);

        $this->addDateWhere($query, $start, $end);

        return (int) $this->dbService->getValue($query);
    }

    /**
     * @return array [string visitDataType => DateTime maxDate]
     */
    private function getMaxDatePerVisitDataType(): array
    {
        $query = (new Builder())->from(GaVisitData::class)
            ->columns([GaVisitData::FIELD_TYPE, 'MAX(' . GaVisitData::FIELD_DATE . ')'])
            ->groupBy(GaVisitData::FIELD_TYPE);

        return array_map(function($date){
            return new DateTime($date);
        }, $this->dbService->getAssoc($query));
    }

    /**
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return int
     */
    private function getMonthlyAverage(DateTime $start = null, DateTime $end = null): int
    {
        $query = (new Builder())
            ->from(GaDayVisit::class)
            ->columns(['ROUND(AVG(' . GaDayVisit::FIELD_VISITS . ') * 365.25 / 12)']);

        $this->addDateWhere($query, $start, $end);

        return (int) $this->dbService->getValue($query);
    }

    /**
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return int
     */
    private function getTotalVisits(DateTime $start = null, DateTime $end = null): int
    {
        $query = (new Builder())
            ->from(GaDayVisit::class)
            ->columns(['SUM(' . GaDayVisit::FIELD_VISITS . ')']);

        $this->addDateWhere($query, $start, $end);

        return (int) $this->dbService->getValue($query);
    }

    /**
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return int
     */
    private function getTotalUniqueVisits(DateTime $start = null, DateTime $end = null): int
    {
        $query = (new Builder())
            ->from(GaDayVisit::class)
            ->columns(['SUM(' . GaDayVisit::FIELD_UNIQUE_VISITS . ')']);

        $this->addDateWhere($query, $start, $end);

        return (int) $this->dbService->getValue($query);
    }

    /**
     * @param string $type
     * @return DateTime|null
     */
    private function getTypeLastUpdate(string $type)
    {
        $query = (new Builder())
            ->from(GaVisitData::class)
            ->where('type = :type:', ['type' => $type])
            ->columns(['MAX(' . GaVisitData::FIELD_DATE . ')']);

        return $this->dbService->getDate($query);
    }

    /**
     * @return array
     */
    private function getVisitDataFromGoogle(): array
    {
        return $this->getVisitorDataFromGoogle(null, null, ["ga:percentNewSessions" => "unique"]);
    }

    /**
     * @param string $dimensionName
     * @param DateTime|null $fromDate
     * @param array $addMetrics
     *
     * @return array
     */
    private function getVisitorDataFromGoogle(string $dimensionName = null, DateTime $fromDate = null, array $addMetrics = []): array
    {
        $fromDate = $fromDate ?: new DateTime('2005-01-01');

        $viewId = (string) $this->config->analytics->viewId;

        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($fromDate->format('Y-m-d'));
        $dateRange->setEndDate("today");

        $sessions = new \Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:visits");
        $sessions->setAlias("visits");

        $metrics = [$sessions];

        foreach ($addMetrics as $metricName => $alias) {
            $metric = new \Google_Service_AnalyticsReporting_Metric();
            $metric->setExpression($metricName);
            $metric->setAlias($alias);

            $metrics[] = $metric;
        }

        $year = new \Google_Service_AnalyticsReporting_Dimension();
        $year->setName("ga:year");

        $month = new \Google_Service_AnalyticsReporting_Dimension();
        $month->setName("ga:month");

        $day = new \Google_Service_AnalyticsReporting_Dimension();
        $day->setName("ga:day");

        $dimensions = [$year, $month, $day];

        if ($dimensionName) {
            $dimension = new \Google_Service_AnalyticsReporting_Dimension();
            $dimension->setName($dimensionName);

            $dimensions[] = $dimension;
        }

        // Create the ReportRequest object.
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics($metrics);
        $request->setDimensions($dimensions);
        $request->setPageSize(999999);

        return $this->requestToArray($request);
    }

    /**
     * Request the data from the given google request and convert it to an array
     *
     * @param \Google_Service_AnalyticsReporting_ReportRequest $request
     * @return array
     */
    private function requestToArray(\Google_Service_AnalyticsReporting_ReportRequest $request): array
    {
        $results = [];

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests(array($request));
        $reports = $this->analytics->reports->batchGet($body);

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

    /**
     * Import various info about visitors
     */
    private function importVisitorData()
    {
        foreach (StatisticsConfig::GA_TYPES as $type => $dimension) {
            $fromDate   = $this->getTypeLastUpdate($type);
            $results    = $this->getVisitorDataFromGoogle($dimension, $fromDate);
            $insertData = [];

            foreach ($results as $resultRow) {
                $date = $resultRow['ga:year'] . '-' . $resultRow['ga:month'] . '-' . $resultRow['ga:day'];

                $insertRow = [
                    GaVisitData::FIELD_DATE   => $date,
                    GaVisitData::FIELD_TYPE   => $type,
                    GaVisitData::FIELD_VALUE  => $resultRow[$dimension],
                    GaVisitData::FIELD_VISITS => $resultRow['visits'],
                ];

                $insertData[] = $insertRow;
            }

            if ($fromDate) {
                $this->dbService->delete(GaVisitData::class, [
                    GaVisitData::FIELD_DATE => $fromDate->format(DbConfig::SQL_DATE_FORMAT),
                    GaVisitData::FIELD_TYPE => $type,
                ]);
            }

            $this->dbService->insertBulk(GaVisitData::class, $insertData);
        }
    }
}