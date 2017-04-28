<?php

namespace KikCMS\Classes\Frontend;

/**
 * Load additional Services from the Website
 */
abstract class WebsiteServicesBase
{
    public abstract function getServices(): array;
}