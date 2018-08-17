<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Config\StatisticsConfig;
use KikCMS\Services\Analytics\AnalyticsService;
use KikCMS\Services\Util\DateTimeService;
use Phalcon\Cache\Backend;

/**
 * @property DateTimeService $dateTimeService
 * @property AnalyticsService $analyticsService
 * @property AccessControl $acl
 * @property Backend $diskCache
 */
class StatisticsController extends BaseController
{
    /**
     * Get data for the visitors graph, based on the user's input
     *
     * @return string
     */
    public function getVisitorsAction()
    {
        if( ! $this->acl->allowed(Permission::ACCESS_STATISTICS)){
            throw new UnauthorizedException();
        }

        $interval = $this->request->getPost('interval', null, StatisticsConfig::VISITS_MONTHLY);
        $start    = $this->dateTimeService->getFromDatePickerValue($this->request->getPost('start'));
        $end      = $this->dateTimeService->getFromDatePickerValue($this->request->getPost('end'));

        $visitorsData   = $this->analyticsService->getVisitorsChartData($interval, $start, $end);
        $visitorData    = $this->analyticsService->getVisitorData($start, $end);
        $overviewData   = $this->analyticsService->getOverviewData($start, $end);
        $requiresUpdate = $this->analyticsService->requiresUpdate();

        return json_encode([
            'visitorsData'   => $visitorsData,
            'visitorData'    => $visitorData,
            'overviewData'   => $overviewData,
            'requiresUpdate' => $requiresUpdate,
        ]);
    }

    /**
     * Update statistics data from google analytics
     */
    public function updateAction()
    {
        $token = $this->request->getPost('token');

        if( ! $this->diskCache->exists($token)){
            throw new UnauthorizedException();
        }

        $this->diskCache->delete($token);

        if($this->analyticsService->isUpdating()){
            while ($this->analyticsService->isUpdating()){
                sleep(1);
            }

            return true;
        }

        $maxDate = $this->analyticsService->getMaxDate();

        return json_encode([
            'success' => $this->analyticsService->importIntoDb(),
            'maxDate' => $maxDate ? $maxDate->format(KikCMSConfig::DATE_FORMAT) : null,
        ]);
    }
}