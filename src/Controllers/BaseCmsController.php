<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\ObjectLists\MenuGroupMap;
use KikCMS\Services\AssetService;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\LanguageService;
use KikCMS\Services\UserService;

/**
 * Controller for the CMS that can render the menu
 *
 * @property AssetService $assetService
 * @property CmsService $cmsService
 * @property LanguageService $languageService
 * @property UserService $userService
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

        $this->flash->setAutoescape(false);

        if ($this->userService->isLoggedIn()) {
            $menuGroupMap = $this->cmsService->getMenuGroupMap();
        } else {
            $menuGroupMap = new MenuGroupMap();
        }

        if ($customCss = $this->websiteSettings->getCustomCss()) {
            $this->assetService->addCss($customCss);
        }

        $this->view->userEmail    = $this->userService->getUser()->email;
        $this->view->menuGroupMap = $menuGroupMap;

        $this->highlightMenuItem($this->dispatcher->getActionName());
    }

    /**
     * @inheritdoc
     */
    protected function setDefaultLanguageCode()
    {
        $this->translator->setLanguageCode($this->languageService->getDefaultCmsLanguageCode());
    }

    /**
     * @param string $menuItem
     */
    protected function highlightMenuItem(string $menuItem)
    {
        $this->view->actionName = $menuItem;
    }
}
