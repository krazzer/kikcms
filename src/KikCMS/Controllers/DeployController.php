<?php

namespace KikCMS\Controllers;

use KikCMS\Services\DeployService;
use Phalcon\Config;
use Phalcon\Mvc\Controller;

/**
 * @property DeployService $deployService
 * @property Config $config
 */
class DeployController extends Controller
{
    public function indexAction()
    {
        echo 'Attempt deploy...';

        $this->deployService->deploy();
    }
}
