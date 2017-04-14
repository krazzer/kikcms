<?php

namespace KikCMS\Modules;

use KikCMS\Plugins\FrontendNotFoundPlugin;
use Phalcon\Events\Manager;
use Phalcon\Loader;
use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Frontend implements ModuleDefinitionInterface
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

            $eventsManager = new Manager;
            $eventsManager->attach('dispatch:beforeException', new FrontendNotFoundPlugin());

            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });
    }
}