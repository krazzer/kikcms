<?php

namespace KikCMS\Controllers;


use DateTime;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Config\StatisticsConfig;
use KikCMS\Services\Analytics\AnalyticsService;
use KikCMS\Services\Util\DateTimeService;

/**
 * @property DateTimeService $dateTimeService
 * @property AnalyticsService $analyticsService
 */
class StatisticsController extends BaseCmsController
{
    /**
     * Show the website's visitors
     */
    public function statsIndexAction()
    {
        $this->view->title = $this->translator->tl('menu.item.statsIndex');

        $startDate = $this->dateTimeService->getOneYearAgoFirstDayOfMonth();
        $maxDate   = $this->analyticsService->getMaxDate() ?: new DateTime();
        $minDate   = $this->analyticsService->getMinDate() ?: new DateTime();

        if ($startDate < $minDate) {
            $startDate = null;
        }

        $this->view->jsTranslations = array_merge($this->view->jsTranslations, [
            'statistics.fetchingNewData',
            'statistics.fetchingFailed',
            'statistics.fetchNewData',
            'statistics.visitors',
        ]);

        $this->view->settings = [
            'dateFormat' => $this->translator->tl('system.momentJsDateFormat'),
            'startDate'  => $startDate ? $startDate->format(KikCMSConfig::DATE_FORMAT) : null,
            'maxDate'    => $maxDate->format(KikCMSConfig::DATE_FORMAT),
            'minDate'    => $minDate->format(KikCMSConfig::DATE_FORMAT),
        ];

        $this->view->pick('cms/statistics');
    }

    /**
     * Get data for the visitors graph, based on the user's input
     *
     * @return string
     */
    public function getVisitorsAction()
    {
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
        if($this->analyticsService->isUpdating()){
            while ($this->analyticsService->isUpdating()){
                sleep(1);
            }

            return true;
        }

        return json_encode([
            'success' => $this->analyticsService->importIntoDb(),
            'maxDate' => $this->analyticsService->getMaxDate()->format(KikCMSConfig::DATE_FORMAT),
        ]);
    }
}