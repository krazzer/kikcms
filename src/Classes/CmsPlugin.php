<?php

namespace KikCMS\Classes;

use ReflectionClass;

abstract class CmsPlugin
{
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