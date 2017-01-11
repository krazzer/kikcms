<?php

namespace KikCMS\Controllers;


class ErrorsController extends BaseCmsController
{
    public function initialize()
    {
        parent::initialize();

        $this->view->hideMenu = $this->request->isAjax();
    }

    public function show404Action()
    {
        $this->response->setStatusCode(404);
    }

    public function show401Action()
    {
        $this->response->setStatusCode(401);
    }

    public function show500Action()
    {
        $this->response->setStatusCode(500);
    }
}
