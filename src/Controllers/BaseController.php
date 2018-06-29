<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use KikCMS\Classes\Translator;
use KikCMS\Util\ByteUtil;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Url;
use Phpcsp\Security\ContentSecurityPolicyHeaderBuilder;

/**
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
        $errorTranslations = $this->translator->getCmsTranslationGroupKeys('error');
        $jsTranslations    = array_merge($errorTranslations, [
            'system.langCode',
            'pages.warningTemplateChange',
            'media.uploadMaxFilesWarning',
            'media.uploadMaxFileSizeWarning',
            'media.fileTypeWarning',
        ]);

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
     * Initialize the language
     */
    public function initializeLanguage()
    {
        if ($langCode = $this->request->getPost('activeLangCode')) {
            $this->translator->setLanguageCode($langCode);
        } else {
            $this->translator->setLanguageCode($this->config->application->defaultLanguage);
        }
    }

    /**
     * Outputs a file with headers so the browser should cache
     *
     * @param string $filePath
     * @param string $mimeType
     * @param string|null $fileName
     *
     * @return string
     */
    protected function outputFile(string $filePath, string $mimeType, string $fileName = null)
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
     * Set Content Security Policy headers
     */
    private function initializeCpsHeaders()
    {
        if( ! $this->config->application->enableCsp){
            return;
        }

        $nonce = uniqid();

        $this->view->cspNonce = $nonce;

        $policy = (new ContentSecurityPolicyHeaderBuilder);
        $policy->addSourceExpression(ContentSecurityPolicyHeaderBuilder::DIRECTIVE_SCRIPT_SRC, "'self'");
        $policy->addNonce(ContentSecurityPolicyHeaderBuilder::DIRECTIVE_SCRIPT_SRC, $nonce);

        $policy->enforcePolicy(false);
        $policy->setReportUri($this->url->getBaseUri() . 'csp/report');

        foreach ($policy->getHeaders(true) as $header) {
            header(sprintf('%s: %s', $header['name'], $header['value']));
        }
    }
}