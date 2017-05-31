<?php

namespace KikCMS\Controllers;


use Phalcon\Mvc\Controller;

class StatisticsController extends Controller
{
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

        return json_encode($this->analyticsService->importIntoDb());
    }
}