<?php

namespace KikCMS\Controllers;

use Phalcon\Mvc\Controller;

class BaseController extends Controller
{
    public function initialize()
    {
        $this->view->setVar("flash", $this->flash);
    }
}