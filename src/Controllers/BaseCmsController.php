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
        $this->highlightMenuItem($this->dispatcher->getActionName());
    }

    /**
     * @param string $menuItem
     */
    protected function highlightMenuItem(string $menuItem)
    {
        $this->view->setVar("actionName", $menuItem);
    }
}
