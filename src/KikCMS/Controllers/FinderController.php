<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\DbService;
use KikCMS\Classes\Finder\Finder;

/**
 * @property DbService dbService
 */
class FinderController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->view->disable();
    }

    /**
     * @return string
     */
    public function uploadAction()
    {
        $finder = new Finder();

        $finder->uploadFiles($this->request->getUploadedFiles());

        return '';
    }
}