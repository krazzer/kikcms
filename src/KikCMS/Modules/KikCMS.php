<?php

namespace KikCMS\Modules;

use KikCMS\Classes\Twig;
use KikCMS\Plugins\NotFoundPlugin;
use KikCMS\Plugins\SecurityPlugin;
use Phalcon\Loader;
use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Events\Manager as EventManager;
use Phalcon\Mvc\View;

class KikCMS implements ModuleDefinitionInterface
{
    /**
     * Register a specific autoloader for the module
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces([
            "KikCMS\\Controllers" => __DIR__ . "../Controllers/",
            "KikCMS\\Models"      => __DIR__ . "../Models/",
        ]);

        $loader->register();
    }

    /**
     * Register specific services for the module
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        $di->set("dispatcher", function () {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace("KikCMS\\Controllers");

            $eventsManager = new EventManager;
            $eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin);
            $eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });

        $di->set("view", function () {
            $view = new View();
            $view->setViewsDir(__DIR__ . "/../Views/");
            $view->registerEngines([
                Twig::DEFAULT_EXTENSION => function ($view, $di) {
                    return new Twig($view, $di, [
                        'cache' => false,
                        'debug' => true,
                    ]);

                    //SITE_PATH . '/cache/twig/'
                }
            ]);

            return $view;
        });
    }
}