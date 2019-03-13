<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use KikCMS\Classes\Translator;
use KikCMS\Services\LanguageService;
use KikCMS\Util\ByteUtil;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Url;
use Phpcsp\Security\ContentSecurityPolicyHeaderBuilder;

/**
 * @property LanguageService $languageService
 * @property Translator $translator
 * @property Url $url
 */
class BaseController extends Controller
{
    /**
     * Initialize the controller
     */
    public function initialize()
    {
        $this->initializeLanguage();
        $this->initializeCpsHeaders();

        setlocale(LC_ALL, $this->translator->tl('system.locale'));

        $maxFileUploads    = ini_get('max_file_uploads') ?: 20;
        $maxFileSizeServer = ByteUtil::stringToBytes(ini_get('upload_max_filesize'));
        $maxFileSizeConfig = ByteUtil::stringToBytes($this->config->media->maxFileSize);
        $maxFileSize       = $maxFileSizeServer < $maxFileSizeConfig ? $maxFileSizeServer : $maxFileSizeConfig;
        $maxFileSizeString = ByteUtil::bytesToString($maxFileSize);

        $jsTranslations = [
            'system.langCode',
            'pages.warningTemplateChange',
            'media.uploadMaxFilesWarning',
            'media.uploadMaxFileSizeWarning',
            'media.fileTypeWarning',
        ];

        $jsSettings = [
            'isDev'             => $this->config->application->env == 'dev',
            'baseUri'           => $this->url->getBaseUri(),
            'maxFileUploads'    => $maxFileUploads,
            'maxFileSize'       => $maxFileSize,
            'maxFileSizeString' => $maxFileSizeString,
        ];

        $this->view->setVar("flash", $this->flash);
        $this->view->setVar("baseUri", $this->url->getBaseUri());
        $this->view->setVar("langCode", $this->translator->tl('system.langCode'));
        $this->view->setVar("jsTranslations", $jsTranslations);
        $this->view->setVar("jsSettings", $jsSettings);
    }

    /**
     * Initialize the language
     */
    public function initializeLanguage()
    {
        if ($langCode = $this->request->getPost('activeLangCode')) {
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
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '.csv";');

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

    /**
     * Set Content Security Policy headers
     */
    private function initializeCpsHeaders()
    {
        if ( ! $cspSettings = $this->config->get('csp')) {
            return;
        }

        $nonce = uniqid();

        $this->view->cspNonce = $nonce;

        $allowedDomains = [
            "'self'",
            'cdn.tinymce.com',
            'www.gstatic.com',
        ];

        $policy = (new ContentSecurityPolicyHeaderBuilder);
        $policy->addSourceExpression(ContentSecurityPolicyHeaderBuilder::DIRECTIVE_SCRIPT_SRC, implode(' ', $allowedDomains));
        $policy->addSourceExpression(ContentSecurityPolicyHeaderBuilder::DIRECTIVE_STYLE_SRC, "* 'unsafe-inline'");
        $policy->addNonce(ContentSecurityPolicyHeaderBuilder::DIRECTIVE_SCRIPT_SRC, $nonce);

        $policy->enforcePolicy(false);
        $policy->setReportUri($cspSettings['reportUri']);

        foreach ($policy->getHeaders(true) as $header) {
            header(sprintf('%s: %s', $header['name'], $header['value']));
        }
    }
}