<?php

namespace KikCMS\Services;

use KikCMS\Classes\DbService;
use KikCMS\Classes\ErrorLogHandler;
use KikCMS\Classes\Phalcon\Security;
use KikCMS\Classes\Translator;
use KikCMS\Classes\Phalcon\Twig;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Services\Base\BaseServices;

use Monolog\ErrorHandler;
use Phalcon\Assets\Manager;
use Phalcon\Cache\Backend;
use Phalcon\Cache\Backend\File;
use Phalcon\Cache\Frontend\Json;
use Phalcon\Db;
use Phalcon\DiInterface;
use Phalcon\Mvc\View;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
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

        $url = new UrlProvider();
        $url->setBaseUri($baseUrl);

        return $url;
    }

    protected function initView()
    {
        $view = new View();
        $view->setViewsDir(__DIR__ . "/../Views/");
        $view->registerEngines([
            Twig::DEFAULT_EXTENSION => function (View $view, DiInterface $di) {
                $env   = $di->get('config')->get('application')->get('env');
                $cache = $env == KikCMSConfig::ENV_PROD ? SITE_PATH . '/cache/twig/' : false;

                return new Twig($view, $di, [
                    'cache' => $cache,
                    'debug' => true,
                ], [
                    'kikcms' => $view->getViewsDir()
                ]);
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
            http_response_code(500);

            echo $this->get('view')->getRender('errors', 'show500', [
                'error' => $isProduction ? null : $error,
            ]);
        });

        register_shutdown_function(function() use ($isProduction){
            $error = error_get_last();

            if( ! $error) {
                return;
            }

            http_response_code(500);

            echo $this->get('view')->getRender('errors', 'show500', [
                'error' => $isProduction ? null : $error,
            ]);
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
     * If the configuration specify the use of metadata adapter use it or use memory otherwise
     */
    protected function initModelsMetadata()
    {
        return new MetaData();
    }

    /**
     * @return Backend
     */
    protected function initCache()
    {
        return new File(new Json(["lifetime" => 3600 * 24]), [
            'cacheDir' => SITE_PATH . 'cache/cache/'
        ]);
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
}
