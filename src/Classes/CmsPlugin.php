<?php

namespace KikCMS\Classes;

use Phalcon\Mvc\Router\Group;
use ReflectionClass;

abstract class CmsPlugin
{
    /**
     * @param Group $backend
     */
    public function addBackendRoutes(Group $backend)
    {

    }

    /**
     * @param Group $frontend
     */
    public function addFrontendRoutes(Group $frontend)
    {

    }

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

    /**
     * @return string
     */
    public abstract function getName(): string;

    /**
     * @return array
     */
    public function getSimpleServices(): array
    {
        return [];
    }

    /**
     * Adds services
     */
    public function addServices()
    {

    }
}