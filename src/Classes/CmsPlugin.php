<?php

namespace KikCMS\Classes;

use KikCMS\Services\Base\BaseServices;
use Phalcon\Mvc\Router\Group;
use ReflectionClass;

abstract class CmsPlugin
{
    /**
     * @param Group $backend
     */
    public abstract function addBackendRoutes(Group $backend);

    /**
     * @param Group $frontend
     */
    public abstract function addFrontendRoutes(Group $frontend);

    /**
     * @return string
     */
    public abstract function getName(): string;

    /**
     * Adds services
     * @param BaseServices $services
     */
    public abstract function addServices(BaseServices $services);

    /**
     * Simple services are classes that are simply instantiated without any additional parameters
     *
     * @return array
     */
    public abstract function getSimpleServices(): array;

    /**
     * @return string
     */
    public function getControllersNamespace(): string
    {
        return ucfirst($this->getName()) . "Plugin\\Controllers";
    }

    /**
     * @return string
     */
    public function getSourceDirectory(): string
    {
        $class_info = new ReflectionClass($this);
        return dirname($class_info->getFileName());
    }

    /**
     * @return string
     */
    public function getTranslationsDirectory(): string
    {
        return dirname($this->getSourceDirectory()) . '/resources/translations/';
    }
}