<?php

namespace KikCMS\Services;

use KikCMS\Classes\Db\Db;
use KikCMS\Classes\Translator;
use KikCMS\Classes\Twig;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Services\Base\BaseServices;
use Monolog\ErrorHandler;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;
use Phalcon\DiInterface;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\View;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Validation;
use Swift_Mailer;
use Swift_SendmailTransport;

class Services extends BaseServices
{
    /**
     * Register router
     */
    protected function initRouter()
    {
        $router = new Router();

        $router->setDefaultModule("kikcms");

        $router->add("/deploy", [
            "controller" => "deploy",
            "action"     => "index"
        ]);

        $router->add("/cms", [
            "controller" => "cms",
            "action"     => "index"
        ]);

        $router->add("/cms/login", [
            "controller" => "login",
            "action"     => "index"
        ]);

        $router->add("/cms/login/:action", [
            "controller" => "login",
            "action"     => 1
        ]);

        $router->removeExtraSlashes(true);

        return $router;
    }

    /**
     * The URL component is used to generate all kind of urls in the application
     */
    protected function initUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
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
                ]);
            }
        ]);

        return $view;
    }

    /**
     * Database connection is created based in the parameters defined in the configuration file
     *
     * @return Db
     */
    protected function initDb(): Db
    {
        $config = $this->getDatabaseConfig()->toArray();

        $dbClass = Pdo::class . '\\' . $config['adapter'];
        unset($config['adapter']);

        $databaseAdapter = new $dbClass($config);

        return new Db($databaseAdapter);
    }

    /**
     * @return DeployService
     */
    protected function initDeployService()
    {
        return new DeployService();
    }

    /**
     * @return ErrorHandler
     */
    protected function initErrorHandler()
    {
        $webmasterEmail = $this->getApplicationConfig()->webmasterEmail;
        $errorFromMail  = 'error@' . $_SERVER['HTTP_HOST'];

        // initialize error handler
        $mailHandler = new NativeMailerHandler($webmasterEmail, 'Error', $errorFromMail);
        $mailHandler->setContentType('text/html');
        $mailHandler->setFormatter(new HtmlFormatter());

        $log = new Logger('errorlog');
        $log->pushHandler($mailHandler);

        $errorHandler = new ErrorHandler($log);

        // mail errors instead of showing them in production
        if ($this->getApplicationConfig()->env == KikCMSConfig::ENV_PROD) {
            $errorHandler->registerErrorHandler();
            $errorHandler->registerExceptionHandler();
            $errorHandler->registerFatalHandler();
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 'on');
        }

        //todo: show something else when errors are on production

        return $errorHandler;
    }

    /**
     * If the configuration specify the use of metadata adapter use it or use memory otherwise
     */
    protected function initModelsMetadata()
    {
        return new MetaData();
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
        $sendMailCommand = $this->getApplicationConfig()->sendmailCommand;
        $sendMailCommand = $sendMailCommand ?: '/usr/sbin/sendmail -bs';

        $transport = Swift_SendmailTransport::newInstance($sendMailCommand);
        $mailer    = Swift_Mailer::newInstance($transport);

        return new MailService($mailer);
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
