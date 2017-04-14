<?php

namespace KikCMS\Controllers;

use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\UserService;

/**
 * Controller for the CMS that can render the menu
 * @property UserService $userService
 * @property CmsService $cmsService
 */
class BaseCmsController extends BaseController
{
    public function initialize()
    {
        parent::initialize();

        if ($this->userService->isLoggedIn()) {
            $menuStructure = $this->cmsService->getMenuItemGroups();
        } else {
            $menuStructure = [];
        }

        $this->view->setVar("menuStructure", $menuStructure);
        $this->view->setVar("actionName", $this->dispatcher->getActionName());
    }
}
