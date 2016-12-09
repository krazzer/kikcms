<?php

namespace KikCMS\Controllers;

use KikCMS\Services\DeployService;
use Phalcon\Config;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

/**
 * @property DeployService $deployService
 * @property Config $config
 */
class DeployController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $this->deployService->deploy();

        return new Response();
    }
}
