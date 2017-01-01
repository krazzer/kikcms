<?php

namespace KikCMS\Controllers;


class ErrorsController extends BaseCmsController
{
    public function show404Action()
    {
    }

    public function show401Action()
    {
    }

    public function show500Action()
    {
        $this->response->setStatusCode(500);
    }
}
