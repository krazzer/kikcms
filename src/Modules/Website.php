<?php

namespace KikCMS\Modules;

use Phalcon\Loader;
use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Website implements ModuleDefinitionInterface
{
    /**
     * Register a specific autoloader for the module
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces([
            "Website\\Controllers" => __DIR__ . "../Controllers/",
            "Website\\Models"      => __DIR__ . "../Models/",
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
            $dispatcher->setDefaultNamespace("Website\\Controllers");

            return $dispatcher;
        });
    }
}