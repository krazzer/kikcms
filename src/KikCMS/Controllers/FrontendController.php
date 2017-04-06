<?php

namespace KikCMS\Controllers;

class FrontendController extends BaseController
{
    public function pageAction()
    {
        $this->view->pick('@website/base');
    }
}