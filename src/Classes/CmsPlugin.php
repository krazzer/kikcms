<?php declare(strict_types=1);

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
     * @return array
     */
    public function getJsTranslations(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getControllersNamespace(): string
    {
        return $this->getNamespace() . "Controllers";
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return "\\" . ucfirst($this->getName()) . "Plugin\\";
    }

    /**
     * @return string
     */
    public function getSourceDirectory(): string
    {
        return dirname((new ReflectionClass($this))->getFileName());
    }

    /**
     * @return string
     */
    public function getTranslationsDirectory(): string
    {
        return dirname($this->getSourceDirectory()) . '/resources/translations/';
    }

    /**
     * If true, the name of the plugin will be prefixed to all the plugins' services
     * @return bool
     */
    public function prefixServices(): bool
    {
        return true;
    }
}