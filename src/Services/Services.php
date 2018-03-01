<?php

namespace KikCMS\Services;

use KikCMS\Classes\ErrorLogHandler;
use KikCMS\Classes\Finder\FinderFileService;
use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use KikCMS\Classes\Frontend\Extendables\TemplateFieldsBase;
use KikCMS\Classes\Frontend\Extendables\TemplateVariablesBase;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\Monolog\PhalconHtmlFormatter;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\Security;
use KikCMS\Classes\Phalcon\Url;
use KikCMS\Classes\Phalcon\View;
use KikCMS\Classes\ObjectStorage\FileStorage;
use KikCMS\Classes\Translator;
use KikCMS\Classes\Phalcon\Twig;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\FieldStorageService;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\StorageService;
use KikCMS\Config\KikCMSConfig;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\Services\Analytics\AnalyticsService;
use KikCMS\Services\Base\BaseServices;
use KikCMS\Classes\ObjectStorage\File as FileStorageFile;

use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\DataTable\PagesDataTableService;
use KikCMS\Services\DataTable\TinyMceService;
use KikCMS\Services\Finder\FinderService;
use KikCMS\Services\Pages\FullPageService;
use KikCMS\Services\Pages\PageContentService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\TemplateService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\Util\DateTimeService;
use KikCMS\Services\Util\NumberService;
use KikCMS\Services\Website\FrontendHelper;
use KikCMS\Services\Website\MenuService;
use KikCMS\Services\Website\WebsiteService;
use KikCmsCore\Services\DbService;
use Monolog\ErrorHandler;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Assets\Manager;
use Phalcon\Cache\Backend;
use Phalcon\Cache\Backend\Apcu;
use Phalcon\Cache\Backend\File;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Cache\Frontend\Json;
use Phalcon\Db;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\DiInterface;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Validation;

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
            AnalyticsService::class,
            CacheService::class,
            CmsService::class,
            DateTimeService::class,
            DbService::class,
            DeployService::class,
            ImageHandler::class,
            FinderService::class,
            FieldStorageService::class,
            FullPageService::class,
            FrontendHelper::class,
            PageContentService::class,
            PageLanguageService::class,
            PageRearrangeService::class,
            PageService::class,
            PagesDataTableService::class,
            LanguageService::class,
            MenuService::class,
            NumberService::class,
            Security::class,
            StorageService::class,
            TemplateService::class,
            TinyMceService::class,
            TranslationService::class,
            Translator::class,
            UrlService::class,
            UserService::class,
        ];

        return array_merge($services, $this->getWebsiteServices());
    }

    /**
     * @return array
     */
    protected function getExtendableServices(): array
    {
        return [
            MediaResizeBase::class,
            TemplateFieldsBase::class,
            TemplateVariablesBase::class,
            WebsiteSettingsBase::class,
        ];
    }

    /**
     * @return array
     */
    protected function getWebsiteServices(): array
    {
        /** @var WebsiteService $websiteService */
        $websiteServices = $this->get('websiteSettings');

        return $websiteServices->getServices();
    }

    /**
     * @return Memory
     */
    protected function initAcl()
    {
        return (new Permission())->getAcl();
    }

    /**
     * @return \Google_Service_AnalyticsReporting
     */
    protected function initAnalytics()
    {
        $keyFileLocation    = SITE_PATH . 'config/service-account-credentials.json';
        $keyFileEnvLocation = SITE_PATH . 'env/service-account-credentials.json';

        if (is_readable($keyFileEnvLocation)) {
            $keyFileLocation = $keyFileEnvLocation;
        }

        // Create and configure a new client object.
        $client = new \Google_Client();
        $client->setApplicationName("Analytics");
        $client->setAuthConfig($keyFileLocation);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $analytics = new \Google_Service_AnalyticsReporting($client);

        return $analytics;
    }

    /**
     * @return Backend|false
     */
    protected function initCache()
    {
        if ($this instanceof Cli || isset($_GET['nocache'])) {
            return false;
        }

        $options = null;

        // set the current domain as prefix to prevent caching overlap
        if($this->getApplicationConfig()->env == KikCMSConfig::ENV_DEV){
            $options = ["prefix" => explode('.',$_SERVER['SERVER_NAME'])[0] . ':'];
        }

        return new Apcu(new Data(), $options);
    }

    /**
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
     * @return Backend
     */
    protected function initDiskCache()
    {
        return new File(new Json(["lifetime" => 3600 * 24]), [
            'cacheDir' => SITE_PATH . 'cache/cache/'
        ]);
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
     * @return Logger
     */
    protected function initLogger()
    {
        $isProduction = $this->getApplicationConfig()->env == KikCMSConfig::ENV_PROD;

        $logger = new Logger('logger');

        if ($isProduction) {
            $developerEmail = $this->getApplicationConfig()->developerEmail;
            $errorFromMail  = 'error@' . $_SERVER['HTTP_HOST'];

            $handler = new NativeMailerHandler($developerEmail, 'Error', $errorFromMail, Logger::NOTICE);
            $handler->setContentType('text/html');
            $handler->setFormatter(new PhalconHtmlFormatter());

            $logger->pushHandler($handler);
        }

        $logger->pushHandler(new ErrorLogHandler());

        $logger->pushProcessor(function ($record) {
            $record['extra'] = [
                'Post Data' => $_POST,
                'URL'       => $_SERVER['REQUEST_URI'],
            ];

            return $record;
        });

        return $logger;
    }

    /**
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
     * Start the session the first time some component request the session service
     */
    protected function initSession()
    {
        $session = new SessionAdapter();
        $session->start();

        return $session;
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

    /**
     * @return Validation
     */
    protected function initValidation()
    {
        $validation = new Validation();

        $webFormMessagesKeys = $this->get('translator')->getCmsTranslationGroupKeys('webform.messages');

        $defaultMessages = [];

        foreach ($webFormMessagesKeys as $key) {
            $defaultMessages[last(explode('.', $key))] = $this->get('translator')->tl($key);
        }

        $validation->setDefaultMessages($defaultMessages);

        return $validation;
    }

    /**
     * @return View
     */
    protected function initView()
    {
        $cmsViewDir      = __DIR__ . '/../Views/';
        $siteViewDir     = SITE_PATH . 'app/Views/';
        $cmsResourceDir  = __DIR__ . '/../../resources/';
        $siteResourceDir = SITE_PATH . 'public_html';

        $namespaces = [
            'kikcms'        => $cmsViewDir,
            'website'       => $siteViewDir,
            'cmsResources'  => $cmsResourceDir,
            'siteResources' => $siteResourceDir,
        ];

        $namespaces = array_merge($namespaces, $this->getPluginTwigNamespaces());

        $view = new View();
        $view->setViewsDir($cmsViewDir);
        $view->setNamespaces($namespaces);
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
     * @return WebsiteService
     */
    protected function initWebsiteService()
    {
        return new WebsiteService();
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

    /**
     * @return array
     */
    private function getPluginTwigNamespaces(): array
    {
        $namespaces = [];

        /** @var CmsPluginList $pluginsList */
        $pluginsList = $this->get('websiteSettings')->getPluginList();

        foreach ($pluginsList as $plugin) {
            $name           = 'cms' . ucfirst($plugin->getName());
            $viewsDirectory = $plugin->getSourceDirectory() . '/Views/';

            if (file_exists($viewsDirectory)) {
                $namespaces[$name] = $viewsDirectory;
            }
        }

        return $namespaces;
    }
}
