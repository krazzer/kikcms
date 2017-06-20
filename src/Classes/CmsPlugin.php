<?php

namespace KikCMS\Classes;

abstract class CmsPlugin
{
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