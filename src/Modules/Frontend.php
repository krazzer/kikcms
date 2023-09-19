<?php declare(strict_types=1);

namespace KikCMS\Modules;

use KikCMS\Plugins\FrontendNotFoundPlugin;
use KikCMS\Plugins\ParamConverterPlugin;
use Phalcon\Di\DiInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Frontend implements ModuleDefinitionInterface
{
    protected string $defaultNamespace = "KikCMS\\Controllers";

    /**
     * @inheritdoc
     */
    public function registerAutoloaders(DiInterface $container = null)
    {
        // nothing else needed
    }

    /**
     * @inheritdoc
     */
    public function registerServices(DiInterface $container): void
    {
        $defaultNameSpace = $this->defaultNamespace;

        $container->set("dispatcher", function () use ($defaultNameSpace) {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace($defaultNameSpace);

            $eventsManager = new Manager;
            $eventsManager->attach('dispatch:beforeException', new FrontendNotFoundPlugin);
            $eventsManager->attach("dispatch:beforeDispatchLoop", new ParamConverterPlugin);

            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });
    }
}