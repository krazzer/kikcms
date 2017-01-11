<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\DataTable\DataTable;
use Phalcon\Mvc\Controller;

class BaseController extends Controller
{
    public function initialize()
    {
        $this->view->setVar("flash", $this->flash);
        $this->view->setVar("webmasterEmail", $this->applicationConfig->webmasterEmail);
        $this->view->setVar("dataTableJsTranslations", DataTable::JS_TRANSLATIONS);
    }
}