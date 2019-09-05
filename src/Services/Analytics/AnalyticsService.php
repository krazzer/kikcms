<?php
declare(strict_types=1);

namespace KikCMS\Services\Analytics;


use DateTime;
use KikCMS\Classes\Phalcon\Cache;
use KikCMS\Classes\Phalcon\KeyValue;
use KikCMS\Classes\Translator;
use KikCmsCore\Services\DbService;
use KikCMS\Config\CacheConfig;
use KikCmsCore\Config\DbConfig;
use KikCMS\Config\StatisticsConfig;
use KikCMS\Models\Analytics\GaDayVisit;
use KikCMS\Models\Analytics\GaVisitData;
use Monolog\Logger;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property \Google_Service_AnalyticsReporting $analytics
 * @property AnalyticsImportService $analyticsImportService
 * @property AnalyticsGoogleService $analyticsGoogleService
 * @property DbService $dbService
 * @property Cache $cache
 * @property KeyValue $keyValue
 * @property Translator $translator
 * @property Logger $logger
 */
class AnalyticsService extends Injectable
{
    /**
     * Fetches statics from Google, save them in the DB
     * @return bool
     */
    public function importIntoDb(): bool
    {
        if ($this->isUpdating()) {
            return true;
        }

        $this->keyValue->save(CacheConfig::STATS_UPDATE_IN_PROGRESS, true);

        $this->db->begin();

        try {
            $results       = $this->analyticsGoogleService->getVisitData();
            $requireUpdate = $this->analyticsImportService->importVisitorMetrics();

            $results = array_map(function ($row) {
                return [
                    GaDayVisit::FIELD_DATE          => $row['ga:year'] . '-' . $row['ga:month'] . '-' . $row['ga:day'],
                    GaDayVisit::FIELD_VISITS        => (int) $row['visits'],
                    GaDayVisit::FIELD_UNIQUE_VISITS => (int) $row['visits'] * ($row['unique'] / 100),
                ];
            }, $results);

            $this->dbService->truncate(GaDayVisit::class);
            $this->dbService->insertBulk(GaDayVisit::class, $results);

            if ( ! $requireUpdate) {
                $this->stopUpdatingForSixHours();
            }
        } catch (\Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception);
            $this->db->rollback();
            $this->keyValue->delete(CacheConfig::STATS_UPDATE_IN_PROGRESS);
            $this->cache->delete(CacheConfig::STATS_REQUIRE_UPDATE);
            return false;
        }

        $this->keyValue->delete(CacheConfig::STATS_UPDATE_IN_PROGRESS);

        return $this->db->commit();
    }

    /**
     * @return null|DateTime
     */
    public function getMaxDate(): ?DateTime
    {
        $query = (new Builder())->from(GaDayVisit::class)->columns(['MAX(date)']);
        return $this->dbService->getDate($query);
    }

    /**
     * @return null|DateTime
     */
    public function getMinDate(): ?DateTime
    {
        $query = (new Builder())->from(GaDayVisit::class)->columns(['MIN(date)']);
        return $this->dbService->getDate($query);
    }

    /**
     * @param DateTime|null $start
     * @param DateTime|null $end
     * @return array
     */
    public function getOverviewData(?DateTime $start, ?DateTime $end): array
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
            ->groupBy(GaVisitData::FIELD_TYPE . ', ' . GaVisitData::FIELD_VALUE)
            ->orderBy('visits DESC, value ASC')
            ->limit(count(StatisticsConfig::GA_TYPES) * 50);

        $this->addDateWhere($query, $start, $end);

        $results = $query->getQuery()->execute()->toArray();

        foreach ($results as $result) {
            $type = $result[GaVisitData::FIELD_TYPE];

            if ( ! array_key_exists($type, $visitorData)) {
                $visitorData[$type] = [];
            }

            if (count($visitorData[$type]) >= 25) {
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
     * @return bool
     */
    public function isUpdating(): bool
    {
        return $this->keyValue->exists(CacheConfig::STATS_UPDATE_IN_PROGRESS);
    }

    /**
     * Checks if the db is up to date
     *
     * @return bool
     */
    public function requiresUpdate(): bool
    {
        if ($this->cache->get(CacheConfig::STATS_REQUIRE_UPDATE) === false) {
            return false;
        }

        $maxDate = $this->getMaxDate();

        // if there are 0 zero stats, or today isn't present yet
        if ( ! $maxDate || $maxDate->format('dmY') !== (new DateTime)->format('dmY')) {
            return true;
        }

        // if there are no visitor data stats
        if ( ! $typeMaxDates = $this->getMaxDatePerVisitDataType()) {
            return true;
        }

        // if there are no visitor data stats for today
        foreach ($typeMaxDates as $type => $maxDate) {
            if ( ! $maxDate || $maxDate->format('dmY') !== (new DateTime)->format('dmY')) {
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

        return array_map(function ($date) {
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
     * Store cache entry that prevents updates for 6 hours
     */
    private function stopUpdatingForSixHours()
    {
        $this->cache->save(CacheConfig::STATS_REQUIRE_UPDATE, false, CacheConfig::ONE_DAY / 4);
    }
}