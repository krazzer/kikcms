<?php declare(strict_types=1);

namespace KikCMS\Services;

use ErrorException;
use Exception;
use Google_Client;
use Google_Service_AnalyticsReporting;
use KikCMS\Classes\ErrorLogHandler;
use KikCMS\Classes\Exceptions\DatabaseConnectionException;
use KikCMS\Classes\Phalcon\KeyValue;
use KikCMS\Classes\Phalcon\SecuritySingleToken;
use KikCMS\Services\Cms\QueryLogService;
use KikCMS\Services\Finder\FileService;
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
use KikCMS\Config\KikCMSConfig;
use KikCMS\Config\TranslatorConfig;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\Services\Base\BaseServices;
use KikCmsCore\Config\DbConfig;
use KikCmsCore\Exceptions\ResourcesExceededException;
use KikCmsCore\Services\DbService;
use Monolog\ErrorHandler;
use Monolog\Handler\DeduplicationHandler;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Assets\Manager;
use Phalcon\Cache;
use Phalcon\Db\Adapter\AdapterInterface as PdoAdapterInterface;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Db\Adapter\PdoFactory;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\Http\Response\Cookies;
use Phalcon\Security;
use Phalcon\Session\Bag;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Validation;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;
use ReCaptcha\ReCaptcha;
use Swift_Mailer;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Phalcon\Session\Manager as SessionManager;
use Phalcon\Session\Adapter\Stream as SessionAdapter;
use KikCMS\Classes\ObjectStorage\File as FileStorageFile;
use Phalcon\Flash\Session as FlashSession;

class Services extends BaseServices
{
    /**
     * @inheritdoc
     */
    protected function getSimpleServices(): array
    {
        $services = [
            DbService::class,
            ImageHandler::class,
            SecuritySingleToken::class,
            Translator::class,
        ];

        /** @var NamespaceService $namespaceService */
        $namespaceService = $this->get('namespaceService');

        $cmsServices = $namespaceService->getClassNamesByNamespace(KikCMSConfig::NAMESPACE_PATH_CMS_SERVICES);

        return array_merge($services, $cmsServices, $this->getWebsiteSimpleServices());
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
        /** @var NamespaceService $namespaceService */
        $namespaceService = $this->get('namespaceService');

        $websiteServices = $namespaceService->getClassNamesByNamespace(KikCMSConfig::NAMESPACE_PATH_SERVICES);
        $objectServices  = $namespaceService->getClassNamesByNamespace(KikCMSConfig::NAMESPACE_PATH_OBJECTS);

        $services = array_merge($websiteServices, $objectServices);

        $simpleServices = [];

        foreach ($services as $service) {
            if (is_string($service) && (substr($service, -7) == 'Service' || substr($service, -6) == 'Helper')) {
                $simpleServices[] = $service;
            }
        }

        return $simpleServices;
    }

    /**
     * @return Memory
     */
    protected function initAcl(): Memory
    {
        return $this->get('permission')->getAcl();
    }

    /**
     * @return Permission
     */
    protected function initPermission(): Permission
    {
        return new Permission();
    }

    /**
     * @return Google_Service_AnalyticsReporting
     */
    protected function initAnalytics(): Google_Service_AnalyticsReporting
    {
        $keyFileLocation    = $this->getAppConfig()->path . 'config/service-account-credentials.json';
        $keyFileEnvLocation = $this->getAppConfig()->path . 'env/service-account-credentials.json';

        if (is_readable($keyFileEnvLocation)) {
            $keyFileLocation = $keyFileEnvLocation;
        }

        // Create and configure a new client object.
        $client = new Google_Client();
        $client->setApplicationName("Analytics");
        $client->setAuthConfig($keyFileLocation);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);

        return new Google_Service_AnalyticsReporting($client, $this->getIniConfig()->analytics->url ?? null);
    }

    /**
     * @return Cache|null
     */
    protected function initCache(): ?Cache
    {
        if ( ! $config = (array) $this->getIniConfig()->cache->toArray() ?? null) {
            return null;
        }

        /** @noinspection PhpInstanceofIsAlwaysTrueInspection */
        if ($this instanceof Cli && $config['adapter'] == 'apcu') {
            return null;
        }

        if (isset($_GET['nocache'])) {
            return null;
        }

        if (isset($config['cacheDir'])) {
            $config['cacheDir'] = $this->getIniConfig()->application->path . $config['cacheDir'];
        }

        // set the current port as prefix to prevent caching overlap
        if ($this->getIniConfig()->isDev() && isset($_SERVER['SERVER_PORT'])) {
            $config["prefix"] = $_SERVER['SERVER_PORT'] . ':' . ($config["prefix"] ?? '');
        }

        $config['defaultSerializer'] = 'Php';

        $serializerFactory = new SerializerFactory();
        $adapterFactory    = new AdapterFactory($serializerFactory);

        $adapter = $adapterFactory->newInstance($config['adapter'], $config);

        return new Cache($adapter);
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
     * @return PdoAdapterInterface
     */
    protected function initDb(): PdoAdapterInterface
    {
        $config = $this->getDbConfig()->toArray();

        try {
            $db = (new PdoFactory)->load(['adapter' => $config['adapter'], 'options' => $config]);
        } catch (Exception $exception) {
            if ($exception->getCode() == DbConfig::ERROR_CODE_TOO_MANY_USER_CONNECTIONS) {
                $this->get('logger')->log(Logger::WARNING, $exception);
                throw new ResourcesExceededException;
            } else {
                $this->get('logger')->log(Logger::WARNING, $exception);
                throw new DatabaseConnectionException;
            }
        }

        $db->setEventsManager($this->get('eventsManager'));

        if ($config['logqueries'] ?? false) {
            /** @var QueryLogService $queryLogService */
            $queryLogService = $this->get('queryLogService');
            $queryLogService->setup();
        }

        return $db;
    }

    /**
     * @return Cache
     */
    protected function initKeyValue(): Cache
    {
        $config['defaultSerializer'] = 'Json';

        $serializerFactory = new SerializerFactory();
        $adapterFactory    = new AdapterFactory($serializerFactory);

        $adapter = $adapterFactory->newInstance('stream', [
            'storageDir' => $this->getAppConfig()->path . 'storage/keyvalue/'
        ]);

        $keyValue = new KeyValue($adapter);
        $keyValue->setMemoryCache($this->getShared('cache'));

        return $keyValue;
    }

    /**
     * @return ErrorHandler
     */
    protected function initErrorHandler(): ErrorHandler
    {
        $errorHandler = new ErrorHandler($this->get('logger'));

        set_exception_handler(function ($error) {
            /** @var ErrorService $errorService */
            $errorService = $this->get('errorService');
            $errorService->handleError($error);
        });

        // handle warnings and notices as exceptions on development
        set_error_handler(function ($severity, $message, $file, $line) {
            if ($this->getIniConfig()->isDev()) {
                throw new ErrorException($message, $severity, $severity, $file, $line);
            }
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
        $fileStorage->setStorageDir($this->getAppConfig()->path . 'storage/');

        return $fileStorage;
    }

    /**
     * @return FileService
     */
    protected function initFileService(): FileService
    {
        return new FileService('media', 'thumbs');
    }

    /**
     * Register the flash service with custom CSS classes
     */
    protected function initFlash(): FlashSession
    {
        $flashSession = new FlashSession();

        $flashSession->setCssClasses([
            'error'   => 'alert alert-danger',
            'success' => 'alert alert-success',
            'notice'  => 'alert alert-info',
            'warning' => 'alert alert-warning'
        ]);

        return $flashSession;
    }

    /**
     * @return Logger
     */
    protected function initLogger(): Logger
    {
        $logger = new Logger('logger');

        if ($this->getIniConfig()->isProd() && $developerEmail = $this->getAppConfig()->developerEmail) {
            $errorFromMail = 'error@' . $_SERVER['HTTP_HOST'];

            $handler = new NativeMailerHandler($developerEmail, 'Error', $errorFromMail, Logger::NOTICE);
            $handler->setContentType('text/html');
            $handler->setFormatter(new PhalconHtmlFormatter());

            $logger->pushHandler(new DeduplicationHandler($handler));
        }

        $logger->pushHandler(new ErrorLogHandler());

        $logger->pushProcessor(function ($record) {
            $record['extra'] = [
                'Post Data' => $_POST,
                'URL'       => $_SERVER['REQUEST_URI'] ?? null,
            ];

            return $record;
        });

        return $logger;
    }

    /**
     * @return Swift_Mailer
     */
    protected function initMailer(): Swift_Mailer
    {
        $transport = new Swift_SendmailTransport();

        if ($mailerConfig = $this->getConfig('mailer')) {
            if ($mailerConfig->host && $mailerConfig->port) {
                $transport = new Swift_SmtpTransport($mailerConfig->host, $mailerConfig->port);
            }
        }

        return new Swift_Mailer($transport);
    }

    /**
     * @return MailService
     */
    protected function initMailService(): MailService
    {
        return new MailService($this->get('mailer'));
    }

    /**
     * @return ReCaptcha
     */
    protected function initReCaptcha(): ReCaptcha
    {
        $secret = $this->getConfig('recaptcha', 'secret');

        return new ReCaptcha($secret);
    }

    /**
     * Start the session the first time some component request the session service
     * @return SessionManager
     */
    protected function initSession(): SessionManager
    {
        $session = new SessionManager();
        $files   = new SessionAdapter(['savePath' => '/tmp']);

        $session->setAdapter($files);
        $session->start();

        return $session;
    }

    /**
     * @return Bag
     */
    protected function initSessionBag(): Bag
    {
        return new Bag("sessionBag");
    }

    /**
     * @return Security
     */
    protected function initSecurity(): Security
    {
        return new SecuritySingleToken();
    }

    /**
     * @return Translator
     */
    protected function initTranslator(): Translator
    {
        return new Translator($this->get('config')->application->defaultLanguage, [
            TranslatorConfig::LANGUAGE_NL => $this->getAppConfig()->cmsPath . 'resources/translations/nl.php',
            TranslatorConfig::LANGUAGE_EN => $this->getAppConfig()->cmsPath . 'resources/translations/en.php',
        ], [
            TranslatorConfig::LANGUAGE_NL => $this->getAppConfig()->path . 'resources/translations/nl.php',
            TranslatorConfig::LANGUAGE_EN => $this->getAppConfig()->path . 'resources/translations/en.php',
        ]);
    }

    /**
     * @return TwigService
     */
    protected function initTwigService(): TwigService
    {
        return new TwigService(
            $this->getAppConfig()->path . 'storage/media/',
            $this->getAppConfig()->path . $this->getAppConfig()->publicFolder . '/images/icons/'
        );
    }

    /**
     * The URL component is used to generate all kind of urls in the application
     * Note that the baseUri is not set in the CLI
     */
    protected function initUrl(): Url
    {
        $baseUri = $this->get('cmsService')->getBaseUri();

        $url = new Url();
        $url->setBaseUri($baseUri);

        return $url;
    }

    /**
     * @return Validation
     */
    protected function initValidation(): Validation
    {
        return new Validation();
    }

    /**
     * @return View
     */
    protected function initView(): View
    {
        $cmsViewDir      = __DIR__ . '/../Views/';
        $cmsResourceDir  = __DIR__ . '/../../resources/';
        $siteViewDir     = $this->getAppConfig()->path . 'app/Views/';
        $siteResourceDir = $this->getAppConfig()->path . $this->getAppConfig()->publicFolder;

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
            Twig::DEFAULT_EXTENSION => function (View $view) {
                return new Twig($view, $this, [
                    'cache' => $this->getIniConfig()->isDev() ? false : $this->getAppConfig()->path . 'cache/twig/',
                    'debug' => $this->getIniConfig()->isDev(),
                ], $view->getNamespaces());
            }
        ]);

        $view->assets = new Manager();

        return $view;
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
