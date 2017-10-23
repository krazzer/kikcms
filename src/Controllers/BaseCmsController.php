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
    /**
     * @inheritdoc
     */
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
     * @inheritdoc
     */
    public function initializeLanguage()
    {
        if(isset($this->config->application->defaultCmsLanguage)){
            $this->translator->setLanguageCode($this->config->application->defaultCmsLanguage);
        } else {
            $this->translator->setLanguageCode($this->config->application->defaultLanguage);
        }
    }

    /**
     * @param string $menuItem
     */
    protected function highlightMenuItem(string $menuItem)
    {
        $this->view->setVar("actionName", $menuItem);
    }
}
