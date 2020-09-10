<?php
declare(strict_types=1);

namespace KikCMS\Services\Analytics;


use DateTime;
use KikCMS\Config\StatisticsConfig;
use KikCMS\Models\Analytics\GaVisitData;
use KikCmsCore\Config\DbConfig;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property AnalyticsGoogleService $analyticsGoogleService
 * @property DbService $dbService
 */
class AnalyticsImportService extends Injectable
{
    /**
     * Import various info about visitors
     *
     * @return bool
     */
    public function importVisitorMetrics(): bool
    {
        $requireUpdate = false;

        foreach (StatisticsConfig::GA_TYPES as $type => $dimension) {
            if (is_array($dimension)) {
                $filters   = $dimension[1];
                $dimension = $dimension[0];
            } else {
                $filters = [];
            }

            $fromDate    = $this->getTypeLastUpdate($type);
            $visitorData = $this->analyticsGoogleService->getVisitorData($dimension, $fromDate, [], $filters);
            $insertData  = $this->getInsertDataByVisitorData($visitorData, $dimension, $type);

            if ($fromDate) {
                $this->dbService->delete(GaVisitData::class, [
                    GaVisitData::FIELD_DATE => $fromDate->format(DbConfig::SQL_DATE_FORMAT),
                    GaVisitData::FIELD_TYPE => $type,
                ]);
            }

            $this->dbService->insertBulk(GaVisitData::class, $insertData);

            if (count($visitorData) == StatisticsConfig::MAX_IMPORT_ROWS) {
                $requireUpdate = true;
            }
        }

        return $requireUpdate;
    }


    /**
     * @param string $type
     * @return null|DateTime
     */
    private function getTypeLastUpdate(string $type): ?DateTime
    {
        $query = (new Builder)
            ->from(GaVisitData::class)
            ->where('type = :type:', ['type' => $type])
            ->columns(['MAX(' . GaVisitData::FIELD_DATE . ')']);

        return $this->dbService->getDate($query);
    }

    /**
     * @param array $results
     * @param string $dimension
     * @param string $type
     * @return array
     */
    private function getInsertDataByVisitorData(array $results, string $dimension, string $type): array
    {
        $insertData = [];

        foreach ($results as $resultRow) {
            $date  = $resultRow['ga:year'] . '-' . $resultRow['ga:month'] . '-' . $resultRow['ga:day'];
            $value = $resultRow[$dimension];

            if (strlen($value) > 128) {
                $value = substr($value, 0, 115) . uniqid();
            }

            $insertRow = [
                GaVisitData::FIELD_DATE   => $date,
                GaVisitData::FIELD_TYPE   => $type,
                GaVisitData::FIELD_VALUE  => $value,
                GaVisitData::FIELD_VISITS => $resultRow['visits'],
            ];

            $insertData[] = $insertRow;
        }

        return $insertData;
    }
}