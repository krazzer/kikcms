<?php

namespace KikCMS\Services;

use KikCMS\Classes\DbService;
use KikCMS\Classes\ErrorLogHandler;
use KikCMS\Classes\Finder\FinderFileService;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\Phalcon\Security;
use KikCMS\Classes\Phalcon\Url;
use KikCMS\Classes\Phalcon\View;
use KikCMS\Classes\Storage\FileStorage;
use KikCMS\Classes\Translator;
use KikCMS\Classes\Phalcon\Twig;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Services\Base\BaseServices;
use KikCMS\Classes\Storage\File as FileStorageFile;

use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\Pages\PageContentService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\TemplateService;
use KikCMS\Services\Pages\UrlService;
use Monolog\ErrorHandler;
use Phalcon\Assets\Manager;
use Phalcon\Cache\Backend;
use Phalcon\Cache\Backend\Apc;
use Phalcon\Cache\Backend\File;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Cache\Frontend\Json;
use Phalcon\Db;
use Phalcon\DiInterface;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Validation;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;

use Swift_Mailer;
use Swift_SendmailTransport;
use Throwable;

class Services extends BaseServices
{
    /**
     * @inheritdoc
     */
    protected function getSimpleServices(): array
    {
        $services = [
            CacheService::class,
            TemplateService::class,
            PageService::class,
            PageContentService::class,
            PageLanguageService::class,
            PageRearrangeService::class,
            LanguageService::class,
            TranslationService::class,
            UrlService::class,
        ];

        return array_merge($services, $this->getWebsiteServices());
    }

    /**
     * @return array
     */
    protected function getWebsiteServices(): array
    {
        return [];
    }

    /**
     * @return FileStorage
     */
    protected function initFileStorage()
    {
        $fileStorage = new FileStorageFile();
        $fileStorage->setStorageDir(SITE_PATH . 'storage/');

        return $fileStorage;
    }

    /**
     * @return FinderFileService
     */
    protected function initFinderFileService()
    {
        /** @var FileStorage $fileStorage */
        $fileStorage = $this->get('fileStorage');

        $finderFileService = new FinderFileService($fileStorage);
        $finderFileService->setMediaDir('media');
        $finderFileService->setThumbDir('thumbs');

        return $finderFileService;
    }

    /**
     * @return ImageHandler
     */
    protected function initImageHandler()
    {
        return new ImageHandler();
    }

    /**
     * Register router
     */
    protected function initRouter()
    {
        $routing = new Routing();

        return $routing->initialize();
    }

    /**
     * The URL component is used to generate all kind of urls in the application
     */
    protected function initUrl()
    {
        $protocol = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ?
            "https://" : "http://";

        $domainName = $_SERVER['HTTP_HOST'];

        $baseUrl = $protocol . $domainName . $this->getApplicationConfig()->baseUri;

        $url = new Url();
        $url->setBaseUri($baseUrl);

        return $url;
    }

    protected function initView()
    {
        $cmsViewDir  = __DIR__ . '/../Views/';
        $siteViewDir = SITE_PATH . 'app/Views/';

        $view = new View();
        $view->setViewsDir($cmsViewDir);
        $view->setNamespaces([
            'kikcms'  => $cmsViewDir,
            'website' => $siteViewDir
        ]);
        $view->registerEngines([
            Twig::DEFAULT_EXTENSION => function (View $view, DiInterface $di) {
                $env   = $di->get('config')->get('application')->get('env');
                $cache = $env == KikCMSConfig::ENV_PROD ? SITE_PATH . 'cache/twig/' : false;

                return new Twig($view, $di, [
                    'cache' => $cache,
                    'debug' => $env == KikCMSConfig::ENV_DEV,
                ], $view->getNamespaces());
            }
        ]);

        $view->assets = new Manager();

        return $view;
    }

    /**
     * Database connection is created based in the parameters defined in the configuration file
     *
     * @return Db
     */
    protected function initDb()
    {
        $config = $this->getDatabaseConfig()->toArray();

        $dbClass = Pdo::class . '\\' . $config['adapter'];
        unset($config['adapter']);

        $databaseAdapter = new $dbClass($config);

        return $databaseAdapter;
    }

    /**
     * @return DbService
     */
    protected function initDbService(): DbService
    {
        return new DbService();
    }

    /**
     * @return DeployService
     */
    protected function initDeployService(): DeployService
    {
        return new DeployService();
    }

    /**
     * @return ErrorHandler
     */
    protected function initErrorHandler()
    {
        $isProduction = $this->getApplicationConfig()->env == KikCMSConfig::ENV_PROD;
        $errorHandler = new ErrorHandler($this->get('logger'));

        set_exception_handler(function (Throwable $error) use ($isProduction) {
            $this->handleError($error, $isProduction);
        });

        register_shutdown_function(function () use ($isProduction) {
            $this->handleError(error_get_last(), $isProduction);
        });

        $errorHandler->registerExceptionHandler();
        $errorHandler->registerErrorHandler();
        $errorHandler->registerFatalHandler();

        return $errorHandler;
    }

    /**
     * @return Logger
     */
    protected function initLogger()
    {
        $isProduction = $this->getApplicationConfig()->env == KikCMSConfig::ENV_PROD;

        $logger = new Logger('logger');

        if ($isProduction) {
            $webmasterEmail = $this->getApplicationConfig()->webmasterEmail;
            $errorFromMail  = 'error@' . $_SERVER['HTTP_HOST'];

            $handler = new NativeMailerHandler($webmasterEmail, 'Error', $errorFromMail, Logger::NOTICE);
            $handler->setContentType('text/html');
            $handler->setFormatter(new HtmlFormatter());

            $logger->pushHandler($handler);
        }

        $logger->pushHandler(new ErrorLogHandler());

        return $logger;
    }

    /**
     * @return Backend
     */
    protected function initDiskCache()
    {
        return new File(new Json(["lifetime" => 3600 * 24]), [
            'cacheDir' => SITE_PATH . 'cache/cache/'
        ]);
    }

    /**
     * @return Backend
     */
    protected function initCache()
    {
        return new Apc(new Data());
    }

    /**
     * Start the session the first time some component request the session service
     */
    protected function initSession()
    {
        $session = new SessionAdapter();
        $session->start();

        return $session;
    }

    /**
     * Register the flash service with custom CSS classes
     */
    protected function initFlash()
    {
        return new FlashSession([
            'error'   => 'alert alert-danger',
            'success' => 'alert alert-success',
            'notice'  => 'alert alert-info',
            'warning' => 'alert alert-warning'
        ]);
    }

    /**
     * Register Mailer
     *
     * @return MailService
     */
    protected function initMailService()
    {
        $sendMailCommand = $this->getApplicationConfig()->get('sendmailCommand');
        $sendMailCommand = $sendMailCommand ?: '/usr/sbin/sendmail -bs';

        $transport = Swift_SendmailTransport::newInstance($sendMailCommand);
        $mailer    = Swift_Mailer::newInstance($transport);

        return new MailService($mailer);
    }

    /**
     * @return Security
     */
    protected function initSecurity()
    {
        return new Security();
    }

    /**
     * @return Translator
     */
    protected function initTranslator()
    {
        return new Translator();
    }

    /**
     * @return UserService
     */
    protected function initUserService()
    {
        return new UserService();
    }

    /**
     * @return Validation
     */
    protected function initValidation()
    {
        $validation = new Validation();
        $validation->setDefaultMessages($this->initTranslator()->tl('webform.messages'));

        return $validation;
    }

    /**
     * @param mixed $error
     * @param bool $isProduction
     */
    private function handleError($error, bool $isProduction)
    {
        if ( ! $error) {
            return;
        }

        $errorType = is_object($error) ?
            isset($error->type) ? $error->type : null :
            isset($error['type']) ? $error['type'] : null;

        // don't show recoverable errors in production
        if ($isProduction && $errorType && ! in_array($errorType, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            return;
        }

        http_response_code(500);

        if ($this->isAjaxRequest() && ! $isProduction) {
            echo $this->get('view')->getRender('errors', 'error500content', ['error' => $error]);
            return;
        }

        echo $this->get('view')->getRender('errors', 'show500', [
            'error' => $isProduction ? null : $error,
        ]);
    }

    /**
     * @return bool
     */
    private function isAjaxRequest(): bool
    {
        $ajaxHeader = 'HTTP_X_REQUESTED_WITH';

        return ! empty($_SERVER[$ajaxHeader]) && strtolower($_SERVER[$ajaxHeader]) == 'xmlhttprequest';
    }
}
