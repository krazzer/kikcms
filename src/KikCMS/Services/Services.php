<?php

namespace KikCMS\Services;

use KikCMS\Classes\Translator;
use KikCMS\Services\Base\BaseServices;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Events\Manager as EventsManager;

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
     * We register the events manager
     */
    protected function initDispatcher()
    {
        $eventsManager = new EventsManager;

        /**
         * Check if the user is allowed to access certain action using the SecurityPlugin
         */
        $eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin);

        /**
         * Handle exceptions and not-found exceptions using NotFoundPlugin
         */
        $eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

        $dispatcher = new Dispatcher;
        $dispatcher->setEventsManager($eventsManager);

        return $dispatcher;
    }

    /**
     * The URL component is used to generate all kind of urls in the application
     */
    protected function initUrl()
    {
        $url = new UrlProvider();
        $url->setBaseUri($this->get('config')->application->baseUri);
        return $url;
    }

    protected function initView()
    {
        $view = new View();

        $view->setViewsDir(SITE_PATH . $this->get('config')->application->viewsDir);

        $view->registerEngines(array(
            ".volt" => 'volt'
        ));

        return $view;
    }

    /**
     * Setting up volt
     *
     * @param $view
     * @param $di
     *
     * @return VoltEngine
     */
    protected function initSharedVolt($view, $di)
    {
        $volt = new VoltEngine($view, $di);

        $volt->setOptions(array(
            "compiledPath" => SITE_PATH . "cache/volt/"
        ));

        $compiler = $volt->getCompiler();
        $compiler->addFunction('is_a', 'is_a');

        return $volt;
    }

    /**
     * Database connection is created based in the parameters defined in the configuration file
     */
    protected function initDb()
    {
        $config = $this->get('config')->get('database')->toArray();

        $dbClass = Pdo::class . '\\' . $config['adapter'];
        unset($config['adapter']);

        return new $dbClass($config);
    }

    /**
     * @return DeployService
     */
    protected function initDeployService()
    {
        return new DeployService();
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
        return new FlashSession(array(
            'error'   => 'alert alert-danger',
            'success' => 'alert alert-success',
            'notice'  => 'alert alert-info',
            'warning' => 'alert alert-warning'
        ));
    }

    /**
     * Register Mailer
     *
     * @return MailService
     */
    protected function initMailService()
    {
        $sendMailCommand = $this->get('config')->get('application')->get('sendmailCommand');
        $sendMailCommand = $sendMailCommand ?: '/usr/sbin/sendmail -bs';

        $transport = Swift_SendmailTransport::newInstance($sendMailCommand);
        $mailer = Swift_Mailer::newInstance($transport);

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
     * Register a user component
     */
    protected function initElements()
    {
        return new Elements();
    }
}
