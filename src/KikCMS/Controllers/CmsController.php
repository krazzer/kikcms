<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Config\MenuConfig;
use KikCMS\DataTables\Pages;
use KikCMS\DataTables\Products;
use KikCMS\Forms\SettingsForm;
use KikCMS\Models\Language;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\UserService;
use Phalcon\Config;
use Phalcon\Http\Response;

/**
 * @property Config $config
 * @property UserService $userService
 * @property UrlService $urlService
 */
class CmsController extends BaseCmsController
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->response->redirect('cms/' . MenuConfig::MENU_ITEM_PAGES);
    }

    public function pagesAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.pages');
        $this->view->object = (new Pages())->render();
        $this->view->pick('cms/default');
    }

    public function productsAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.products');
        $this->view->object = (new Products())->render();
        $this->view->pick('cms/default');
    }

    public function mediaAction()
    {
        $this->view->title  = $this->translator->tl('media.title');
        $this->view->object = (new Finder())->render();
        $this->view->pick('cms/default');
    }

    public function settingsAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.settings');
        $this->view->object = (new SettingsForm())->render();
        $this->view->pick('cms/default');
    }

    /**
     * @param int $pageLanguageId
     * @throws ObjectNotFoundException
     */
    public function previewAction(int $pageLanguageId)
    {
        /** @var PageLanguage $pageLanguage */
        $pageLanguage = PageLanguage::getById($pageLanguageId);

        if ( ! $pageLanguage) {
            throw new ObjectNotFoundException();
        }

        $url = $this->urlService->getUrlByPageLanguage($pageLanguage);

        $this->response->redirect($url);
    }

    public function testAction()
    {
        $repeat = 1000;

        $s = microtime(true);

        for ($i = 0; $i < $repeat; $i++) {
            Language::find();
        }

        echo(microtime(true) - $s);

        echo '<br><br>';

        $s = microtime(true);

        for ($i = 0; $i < $repeat; $i++) {
            $this->languageService->getLanguages();
        }

        echo(microtime(true) - $s);

        exit;
    }

    public function logoutAction()
    {
        $this->userService->logout();
    }
}
