<?php declare(strict_types=1);

namespace KikCMS\Services;

use ErrorException;
use Exception;
use Google_Client;
use Google_Service_AnalyticsReporting;
use KikCMS\Classes\ErrorLogHandler;
use KikCMS\Classes\Phalcon\SecuritySingleToken;
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
use Phalcon\Cache\Backend\Factory;
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
use Swift_SmtpTransport;
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
            DbService::class,
            ImageHandler::class,
            SecuritySingleToken::class,
            Translator::class,
        ];

        $cmsServices = $this->get('namespaceService')->getClassNamesByNamespace(KikCMSConfig::NAMESPACE_PATH_CMS_SERVICES);

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
        $services        = $this->getWebsiteSettings()->getServices();
        $websiteServices = $this->get('namespaceService')->getClassNamesByNamespace(KikCMSConfig::NAMESPACE_PATH_SERVICES);

        $services = array_merge($services, $websiteServices);

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
     * @return Google_Service_AnalyticsReporting
     */
    protected function initAnalytics()
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
        $analytics = new Google_Service_AnalyticsReporting($client, $this->getIniConfig()->analytics->url ?? null);

        return $analytics;
    }

    /**
     * @return BackendInterface|null
     */
    protected function initCache(): ?BackendInterface
    {
        if( ! $config = (array) $this->getIniConfig()->cache ?? null){
            return null;
        }

        if($this instanceof Cli && $config['adapter'] == 'apcu'){
            return null;
        }

        if (isset($_GET['nocache'])) {
            return null;
        }

        if(isset($config['cacheDir'])){
            $config['cacheDir'] = $this->getIniConfig()->application->path . $config['cacheDir'];
        }

        // set the current domain as prefix to prevent caching overlap
        if ($this->getIniConfig()->isDev() && isset($_SERVER['SERVER_NAME'])) {
            $config["prefix"] = explode('.', $_SERVER['SERVER_NAME'])[0] . ':' . ($config["prefix"] ?? '');
        }

        $config["frontend"] = new Data();

        return Factory::load($config);
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
        $config = $this->getDbConfig()->toArray();

        $dbClass = Pdo::class . '\\' . $config['adapter'];
        unset($config['adapter']);

        try {
            $databaseAdapter = new $dbClass($config);
        } catch (Exception $exception) {
            if ($exception->getCode() == DbConfig::ERROR_CODE_TOO_MANY_USER_CONNECTIONS) {
                $this->get('logger')->log(Logger::WARNING, $exception);
                throw new ResourcesExceededException();
            } else {
                throw $exception;
            }
        }

        return $databaseAdapter;
    }

    /**
     * @return BackendInterface
     */
    protected function initKeyValue()
    {
        $frontend = new Json(["lifetime" => 3600 * 24 * 365 * 1000]);

        return new File($frontend, ['cacheDir' => $this->getAppConfig()->path . 'storage/keyvalue/']);
    }

    /**
     * @return ErrorHandler
     */
    protected function initErrorHandler()
    {
        $errorHandler = new ErrorHandler($this->get('logger'));

        set_exception_handler(function ($error) {
            $this->get('errorService')->handleError($error);
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
     * @return Filter
     */
    protected function initFilter(): Filter
    {
        $filter = new Filter();

        return $filter;
    }

    /**
     * @return FileService
     */
    protected function initFileService()
    {
        return new FileService('media', 'thumbs');
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
    protected function initMailService()
    {
        return new MailService($this->get('mailer'));
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
     * @return Translator
     */
    protected function initTranslator(): Translator
    {
        $translator = new Translator([
            TranslatorConfig::LANGUAGE_NL => $this->getAppConfig()->cmsPath . 'resources/translations/nl.php',
            TranslatorConfig::LANGUAGE_EN => $this->getAppConfig()->cmsPath . 'resources/translations/en.php',
        ], [
            TranslatorConfig::LANGUAGE_NL => $this->getAppConfig()->path . 'resources/translations/nl.php',
            TranslatorConfig::LANGUAGE_EN => $this->getAppConfig()->path . 'resources/translations/en.php',
        ]);

        return $translator;
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
    protected function initValidation(): Validation
    {
        return new Validation();
    }

    /**
     * @return View
     */
    protected function initView()
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
            Twig::DEFAULT_EXTENSION => function (View $view, DiInterface $di) {
                return new Twig($view, $di, [
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

    /**
     * @return string|null
     */
    private function getBaseUri(): ?string
    {
        if ($baseUri = $this->getAppConfig()->get('baseUri')) {
            return $baseUri;
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            return "https://" . $_SERVER['HTTP_HOST'] . '/';
        }

        $pathParts = explode('/', $this->getAppConfig()->path);

        // walk through the path to see if the domain name can be retrieved
        foreach ($pathParts as $part) {
            if (strstr($part, '.')) {
                return "https://" . $part . '/';
            }
        }

        return null;
    }
}
