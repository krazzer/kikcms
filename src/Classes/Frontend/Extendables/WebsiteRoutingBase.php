<?php

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;
use Phalcon\Mvc\Router\Group;

/**
 * Add custom routes to either the frontend or the backend of the website
 */
class WebsiteRoutingBase extends WebsiteExtendable
{
    /**
     * @param Group $backend
     */
    public function addBackendRoutes(Group $backend)
    {

    }

    /**
     * @param Group $backend
     */
    public function addFrontendRoutes(Group $backend)
    {

    }
}