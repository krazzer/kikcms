<?php

namespace KikCMS\Controllers;

use KikCMS\Config\MenuConfig;
use KikCMS\Services\UserService;

/**
 * Controller for the CMS that can render the menu
 * @property UserService $userService
 */
class BaseCmsController extends BaseController
{
    public function initialize()
    {
        parent::initialize();

        $cmsUrl = implode('/', array_merge([$this->dispatcher->getActionName()], $this->dispatcher->getParams()));

        if($this->userService->isLoggedIn()){
            $menuStructure = MenuConfig::MENU_STRUCTURE;
        } else {
            $menuStructure = [];
        }

        $this->view->setVar("menuStructure", $menuStructure);
        $this->view->setVar("currentCmsUrl", $cmsUrl);
    }
}
