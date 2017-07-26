<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Translator;
use KikCMS\Util\ByteUtil;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Url;

/**
 * @property Translator $translator
 * @property Url $url
 */
class BaseController extends Controller
{
    public function initialize()
    {
        setlocale(LC_ALL, $this->translator->tl('system.locale'));

        $maxFileUploads    = ini_get('max_file_uploads') ?: 20;
        $maxFileSize       = ByteUtil::stringToBytes(ini_get('upload_max_filesize') ?: 1024 * 1024 * 20);
        $maxFileSizeString = ByteUtil::bytesToString($maxFileSize);
        $errorTranslations = $this->translator->getCmsTranslationGroupKeys('error');
        $jsTranslations    = array_merge($errorTranslations, ['system.langCode', 'pages.warningTemplateChange']);

        $this->view->setVar("flash", $this->flash);
        $this->view->setVar("baseUri", $this->url->getBaseUri());
        $this->view->setVar("developerEmail", $this->applicationConfig->developerEmail);
        $this->view->setVar("jsTranslations", $jsTranslations);

        $this->view->setVar("langCode", $this->translator->tl('system.langCode'));
        $this->view->setVar("phpDateFormat", $this->translator->tl('system.phpDateFormat'));

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