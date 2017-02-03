<?php

namespace KikCMS\Controllers;

use Phalcon\Mvc\Controller;

class BaseController extends Controller
{
    public function initialize()
    {
        $this->view->setVar("flash", $this->flash);
        $this->view->setVar("webmasterEmail", $this->applicationConfig->webmasterEmail);
        $this->view->setVar("jsTranslations", ['error']);
    }

    /**
     * @param string $filePath
     * @param string $mimeType
     * @param string $fileName
     *
     * @return string
     */
    protected function outputFile(string $filePath, string $mimeType, string $fileName)
    {
        $this->response->setContentType($mimeType);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"');
        $this->response->setHeader('Cache-control', 'max-age=2592000, public');
        $this->response->setHeader('Expires', gmdate('D, d M Y H:i:s', strtotime('+1 years')) . ' GMT');
        $this->response->setHeader('Pragma', 'cache');

        return file_get_contents($filePath);
    }
}