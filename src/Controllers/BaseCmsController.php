<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\ObjectLists\MenuGroupMap;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\UserService;

/**
 * Controller for the CMS that can render the menu
 *
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
            $menuGroupMap = $this->cmsService->getMenuGroupMap();
        } else {
            $menuGroupMap = new MenuGroupMap();
        }

        if ($customCss = $this->websiteSettings->getCustomCss()) {
            $this->view->assets->addCss($customCss);
        }

        $this->view->menuGroupMap = $menuGroupMap;
        $this->highlightMenuItem($this->dispatcher->getActionName());
    }

    /**
     * @inheritdoc
     */
    protected function setDefaultLanguageCode()
    {
        if ( ! isset($this->config->application->defaultCmsLanguage)) {
            parent::setDefaultLanguageCode();
        }

        $this->translator->setLanguageCode($this->config->application->defaultCmsLanguage);
    }

    /**
     * @param string $menuItem
     */
    protected function highlightMenuItem(string $menuItem)
    {
        $this->view->actionName = $menuItem;
    }
}
