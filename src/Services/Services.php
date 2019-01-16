<?php

namespace KikCMS\Services;

use KikCMS\Classes\ErrorLogHandler;
use KikCMS\Classes\Phalcon\SecuritySingleToken;
use KikCMS\Services\Cms\RememberMeService;
use KikCMS\Services\Cms\UserSettingsService;
use KikCMS\Services\DataTable\DataTableFilterService;
use KikCMS\Services\DataTable\NestedSetService;
use KikCMS\Services\DataTable\TableDataService;
use KikCMS\Services\Finder\FinderFileRemoveService;
use KikCMS\Services\Finder\FinderFileService;
use KikCMS\Services\Finder\FinderPermissionHelper;
use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use KikCMS\Classes\Frontend\Extendables\TemplateFieldsBase;
use KikCMS\Classes\Frontend\Extendables\TemplateVariablesBase;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\Monolog\PhalconHtmlFormatter;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\Url;
use KikCMS\Classes\Phalcon\View;
use KikCMS\Classes\ObjectStorage\FileStorage;
use KikCMS\Classes\Translator;
use KikCMS\Classes\Phalcon\Twig;
use KikCMS\Services\Util\QueryService;
use KikCMS\Services\WebForm\RelationKeyService;
use KikCMS\Services\WebForm\StorageService;
use KikCMS\Config\KikCMSConfig;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\Services\Analytics\AnalyticsService;
use KikCMS\Services\Base\BaseServices;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\DataTable\PagesDataTableService;
use KikCMS\Services\DataTable\TinyMceService;
use KikCMS\Services\Finder\FinderPermissionService;
use KikCMS\Services\Finder\FinderService;
use KikCMS\Services\Generator\ClassesGeneratorService;
use KikCMS\Services\Generator\GeneratorService;
use KikCMS\Services\Pages\FullPageService;
use KikCMS\Services\Pages\PageContentService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\TemplateService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\Util\DateTimeService;
use KikCMS\Services\Util\NumberService;
use KikCMS\Services\Util\StringService;
use KikCMS\Services\Website\FrontendHelper;
use KikCMS\Services\Website\MenuService;
use KikCMS\Services\Website\WebsiteService;
use KikCmsCore\Services\DbService;
use Monolog\ErrorHandler;
use Monolog\Handler\DeduplicationHandler;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Assets\Manager;
use Phalcon\Cache\Backend;
use Phalcon\Cache\Backend\Apcu;
use Phalcon\Cache\Backend\File;
use Phalcon\Cache\BackendInterface;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Cache\Frontend\Json;
use Phalcon\Db;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\DiInterface;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Filter;
use Phalcon\Http\Response\Cookies;
use Phalcon\Security;
use Phalcon\Validation;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;
use ReCaptcha\ReCaptcha;
use Swift_Mailer;
use Swift_SendmailTransport;
use Throwable;
use KikCMS\Classes\ObjectStorage\File as FileStorageFile;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Session as FlashSession;

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
            ClassesGeneratorService::class,
            CmsService::class,
            GeneratorService::class,
            DataTableFilterService::class,
            DateTimeService::class,
            DbService::class,
            ImageHandler::class,
            FinderService::class,
            FinderFileRemoveService::class,
            FinderPermissionService::class,
            FinderPermissionHelper::class,
            FullPageService::class,
            FrontendHelper::class,
            PageContentService::class,
            PageLanguageService::class,
            PageRearrangeService::class,
            PageService::class,
            PagesDataTableService::class,
            QueryService::class,
            LanguageService::class,
            MenuService::class,
            ModelService::class,
            NestedSetService::class,
            NumberService::class,
            RelationKeyService::class,
            RememberMeService::class,
            SecuritySingleToken::class,
            StorageService::class,
            StringService::class,
            TableDataService::class,
            TemplateService::class,
            TinyMceService::class,
            TranslationService::class,
            Translator::class,
            TwigService::class,
            UrlService::class,
            UserService::class,
            UserSettingsService::class,
        ];

        return array_merge($services, $this->getWebsiteSimpleServices());
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
    protected function getWebsiteSimpleServices(): array
    {
        /** @var WebsiteSettingsBase $websiteSettings */
        $websiteSettings = $this->get('websiteSettings');

        $services = $websiteSettings->getServices();

        foreach (glob(SITE_PATH . "app/Services/*.php", GLOB_NOSORT) as $filename) {
            $services[] = 'Website\Services\\' . basename(substr($filename, 0, -4));
        }

        $simpleServices = [];

        foreach ($services as $service) {
            if (is_string($service)) {
                $simpleServices[] = $service;
            }
        }

        return $simpleServices;
    }

    /**
     * @return Memory
     */
    protected function initAcl()
    {
        return $this->get('permission')->getAcl();
    }

    /**
     * @return Permission
     */
    protected function initPermission()
    {
        return new Permission();
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
        if ($this->getApplicationConfig()->env == KikCMSConfig::ENV_DEV) {
            $options = ["prefix" => explode('.', $_SERVER['SERVER_NAME'])[0] . ':'];
        }

        return new Apcu(new Data(), $options);
    }

    /**
     * @return Cookies
     */
    protected function initCookies(): Cookies
    {
        $cookies = new Cookies();

        // no need for extra encryption here as TLS is required
        $cookies->useEncryption(false);

        return $cookies;
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
     * @return BackendInterface
     */
    protected function initKeyValue()
    {
        $frontend = new Json(["lifetime" => 3600 * 24 * 365 * 1000]);

        return new File($frontend, ['cacheDir' => SITE_PATH . 'storage/keyvalue/']);
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
     * @return Filter
     */
    protected function initFilter(): Filter
    {
        $filter = new Filter();

        return $filter;
    }

    /**
     * @return FinderFileService
     */
    protected function initFinderFileService()
    {
        return new FinderFileService('media', 'thumbs');
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

            $logger->pushHandler(new DeduplicationHandler($handler));
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
        if ($sendMailCommand = $this->getApplicationConfig()->get('sendmailCommand')) {
            $transport = Swift_SendmailTransport::newInstance($sendMailCommand);
        } else {
            $transport = Swift_SendmailTransport::newInstance();
        }

        $mailer = Swift_Mailer::newInstance($transport);

        return new MailService($mailer);
    }

    /**
     * @return ReCaptcha
     */
    protected function initReCaptcha()
    {
        $secret = $this->getConfig('recaptcha', 'secret');

        return new ReCaptcha($secret);
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
     * @return Security
     */
    protected function initSecurity(): Security
    {
        return new SecuritySingleToken();
    }

    /**
     * The URL component is used to generate all kind of urls in the application
     * Note that the baseUri is not set in the CLI
     */
    protected function initUrl()
    {
        $baseUri = $this->getBaseUri();

        $url = new Url();
        $url->setBaseUri($baseUri);

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
                $isDev = $di->get('config')->get('application')->get('env') == KikCMSConfig::ENV_DEV;
                $cache = $isDev ? false : SITE_PATH . 'cache/twig/';

                return new Twig($view, $di, [
                    'cache' => $cache,
                    'debug' => $isDev,
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

        $recoverableErrorCodes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

        // don't show recoverable errors in production
        if ($isProduction && $errorType && ! in_array($errorType, $recoverableErrorCodes)) {
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

    /**
     * @return string|null
     */
    private function getBaseUri(): ?string
    {
        if ($baseUri = $this->getApplicationConfig()->get('baseUri')) {
            return $baseUri;
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            return "https://" . $_SERVER['HTTP_HOST'] . '/';
        }

        $pathParts = explode('/', SITE_PATH);

        // walk through the path to see if the domain name can be retrieved
        foreach ($pathParts as $part) {
            if (strstr($part, '.')) {
                return "https://" . $part . '/';
            }
        }

        return null;
    }
}
