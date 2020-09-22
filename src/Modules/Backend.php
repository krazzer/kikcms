<?php declare(strict_types=1);

namespace KikCMS\Modules;

use KikCMS\Plugins\BackendNotFoundPlugin;
use KikCMS\Plugins\ParamConverterPlugin;
use KikCMS\Plugins\SecurityPlugin;
use Phalcon\Di\DiInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Backend implements ModuleDefinitionInterface
{
    protected string $defaultNamespace = "KikCMS\\Controllers";

    /**
     * @inheritdoc
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        // nothing else needed
    }

    /**
     * @inheritdoc
     */
    public function registerServices(DiInterface $di)
    {
        $defaultNameSpace = $this->defaultNamespace;

        $di->set("dispatcher", function () use ($defaultNameSpace){
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace($defaultNameSpace);

            $eventsManager = new Manager;
            $eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin);
            $eventsManager->attach('dispatch:beforeException', new BackendNotFoundPlugin);
            $eventsManager->attach("dispatch:beforeDispatchLoop", new ParamConverterPlugin);

            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });
    }
}