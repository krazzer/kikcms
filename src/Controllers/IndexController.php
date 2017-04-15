<?php

namespace KikCMS\Controllers;


class IndexController extends BaseController
{
    public function indexAction()
    {
        return $this->response->redirect('/cms/login');
    }
}