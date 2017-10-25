<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\UserService;

/**
 * Controller for the CMS that can render the menu
 * @property UserService $userService
 * @property CmsService $cmsService
 * @property WebsiteSettingsBase $websiteSettings
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

        if($customCss = $this->websiteSettings->getCustomCss()){
            $this->view->customCss = $customCss;
        }

        $this->view->menuStructure = $menuStructure;
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
        $this->view->actionName = $menuItem;
    }
}
