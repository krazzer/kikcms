<?php

namespace KikCMS\Classes\Frontend\Extendables;

use KikCMS\Classes\Frontend\WebsiteExtendable;

/**
 * Load additional Services from the Website
 */
class WebsiteServicesBase extends WebsiteExtendable
{
    /**
     * @return array
     */
    public function getServices(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getOverloadedServices(): array
    {
        return [];
    }
}