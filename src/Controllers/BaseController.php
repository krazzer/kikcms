<?php declare(strict_types=1);

namespace KikCMS\Controllers;

use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use KikCMS\Classes\Phalcon\Controller;
use KikCMS\Config\MimeConfig;

class BaseController extends Controller
{
    /**
     * Initialize the controller
     */
    public function initialize()
    {
        $this->initializeLanguage();

        setlocale(LC_ALL, $this->translator->tl('system.locale'));

        $maxFileUploads    = ini_get('max_file_uploads') ?: 20;
        $maxFileSizeServer = $this->byteService->stringToBytes(ini_get('upload_max_filesize'));
        $maxFileSizeConfig = $this->byteService->stringToBytes($this->config->media->maxFileSize);
        $maxFileSize       = $maxFileSizeServer < $maxFileSizeConfig ? $maxFileSizeServer : $maxFileSizeConfig;
        $maxFileSizeString = $this->byteService->bytesToString($maxFileSize);

        $jsTranslations = [
            'system.langCode', 'pages.warningTemplateChange', 'media.uploadMaxFilesWarning',
            'media.uploadMaxFileSizeWarning', 'media.fileTypeWarning', 'media.deleteConfirm', 'media.deleteConfirmOne',
            'media.createFolder', 'media.defaultFolderName', 'media.editFileName', 'dataTable.delete.confirmOne',
            'dataTable.delete.confirm', 'dataTable.closeWarning', 'dataTable.switchWarning', 'dataTable.restoreConfirm',
            'statistics.fetchingNewData', 'statistics.fetchingFailed', 'statistics.fetchNewData', 'statistics.visitors',
        ];

        $translations = [];

        foreach ($jsTranslations as $translation) {
            $translations[$translation] = $this->translator->tl($translation);
        }

        $jsSettings = [
            'isDev'             => $this->config->isDev(),
            'baseUri'           => $this->url->getBaseUri(),
            'maxFileUploads'    => $maxFileUploads,
            'maxFileSize'       => $maxFileSize,
            'maxFileSizeString' => $maxFileSizeString,
            'translations'      => $translations,
            'allowedExt'        => MimeConfig::UPLOAD_ALLOW_DEFAULT
        ];

        $this->view->setVar("flash", $this->flash);
        $this->view->setVar("baseUri", $this->url->getBaseUri());
        $this->view->setVar("langCode", $this->translator->tl('system.langCode'));
        $this->view->setVar("cmsTitlePrefix", $this->config->application->cmsTitlePrefix);
        $this->view->setVar("jsSettings", $jsSettings);
    }

    /**
     * Initialize the language
     */
    protected function initializeLanguage()
    {
        $langCode = $this->request->getPost('activeLangCode');

        if ($langCode && $this->translator->languageExists($langCode)) {
            $this->translator->setLanguageCode($langCode);
        } else {
            $this->setDefaultLanguageCode();
        }
    }

    /**
     * Outputs a file with headers so the browser should cache
     *
     * @param string $filePath
     * @param string $mimeType
     * @param string|null $fileName
     * @return string
     */
    protected function outputFile(string $filePath, string $mimeType, string $fileName = null): string
    {
        if ( ! $fileName) {
            $fileName = basename($filePath);
        }

        if ( ! file_exists($filePath)) {
            throw new ObjectNotFoundException($fileName);
        }

        $this->response->setContentType($mimeType);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"');
        $this->response->setHeader('Cache-control', 'max-age=2592000, public');
        $this->response->setHeader('Expires', gmdate('D, d M Y H:i:s', strtotime('+1 years')) . ' GMT');
        $this->response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT');
        $this->response->setHeader('Pragma', 'cache');

        return file_get_contents($filePath);
    }

    /**
     * @param string $fileName
     * @param array $lines
     * @param array $headerLines
     */
    protected function outputCsv(string $fileName, array $lines, array $headerLines = [])
    {
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '.csv";');

        $f = fopen('php://output', 'w');

        if ($headerLines) {
            fputcsv($f, $headerLines, ';');
        }

        foreach ($lines as $line) {
            fputcsv($f, $line, ';');
        }

        fclose($f);
    }

    /**
     * Set the language to default
     */
    protected function setDefaultLanguageCode()
    {
        $this->translator->setLanguageCode($this->languageService->getDefaultLanguageCode());
    }
}