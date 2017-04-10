<?php

namespace KikCMS\Controllers;

use KikCMS\Util\ByteUtil;
use Phalcon\Mvc\Controller;

class BaseController extends Controller
{
    public function initialize()
    {
        $maxFileUploads    = ini_get('max_file_uploads');
        $maxFileSize       = ByteUtil::stringToBytes(ini_get('upload_max_filesize'));
        $maxFileSizeString = ByteUtil::bytesToString($maxFileSize);

        $this->view->setVar("flash", $this->flash);
        $this->view->setVar("webmasterEmail", $this->applicationConfig->webmasterEmail);
        $this->view->setVar("jsTranslations", ['error', 'system.langCode', 'pages.warningTemplateChange']);
        $this->view->setVar("langCode", $this->translator->tl('system.langCode'));

        $this->view->setVar("maxFileUploads", $maxFileUploads);
        $this->view->setVar("maxFileSize", $maxFileSize);
        $this->view->setVar("maxFileSizeString", $maxFileSizeString);
    }

    /**
     * Outputs a file with headers so the browser should cache
     *
     * @param string $filePath
     * @param string $mimeType
     * @param string $fileName
     *
     * @return string
     */
    protected function outputFile(string $filePath, string $mimeType, string $fileName)
    {
        if ( ! file_exists($filePath)) {
            $this->response->setStatusCode(404);
            return 'Object not found';
        }

        $this->response->setContentType($mimeType);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"');
        $this->response->setHeader('Cache-control', 'max-age=2592000, public');
        $this->response->setHeader('Expires', gmdate('D, d M Y H:i:s', strtotime('+1 years')) . ' GMT');
        $this->response->setHeader('Pragma', 'cache');

        return file_get_contents($filePath);
    }
}