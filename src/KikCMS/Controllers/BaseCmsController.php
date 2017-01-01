<?php

namespace KikCMS\Controllers;

use KikCMS\Config\MenuConfig;

/**
 * Controller for the CMS that can render the menu
 */
class BaseCmsController extends BaseController
{
    public function initialize()
    {
        parent::initialize();

        $cmsUrl = implode('/', array_merge([$this->dispatcher->getActionName()], $this->dispatcher->getParams()));

        $this->view->setVar("menuStructure", MenuConfig::MENU_STRUCTURE);
        $this->view->setVar("currentCmsUrl", $cmsUrl);
    }
}
