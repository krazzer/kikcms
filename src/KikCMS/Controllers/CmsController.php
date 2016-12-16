<?php

namespace KikCMS\Controllers;

use Phalcon\Config;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

/**
 * @property Config $config
 */
class CmsController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        echo 'Hey, you there!';
    }
}
