<?php


namespace KikCMS\Controllers;


use DateTime;
use KikCMS\Config\MenuConfig;
use KikCMS\Services\CacheService;
use KikCMS\Util\ByteUtil;
use Phalcon\Cache\Backend;
use Phalcon\Http\ResponseInterface;

/**
 * @property CacheService $cacheService
 * @property Backend $cache
 */
class CacheController extends BaseCmsController
{
    /**
     * Display control panel for APCu cache
     */
    public function managerAction()
    {
        $cacheInfo = apcu_cache_info();
        $startTime = (new DateTime())->setTimestamp($cacheInfo['start_time']);

        $this->view->title            = 'Cache beheer';
        $this->view->cacheInfo        = $cacheInfo;
        $this->view->uptime           = $startTime->diff(new DateTime());
        $this->view->memorySize       = ByteUtil::bytesToString($cacheInfo['mem_size']);
        $this->view->selectedMenuItem = MenuConfig::MENU_ITEM_SETTINGS;
        $this->view->cacheNodeMap     = $this->cacheService->getCacheNodeMap();

        $this->view->pick('cms/cacheManager');
    }

    /**
     * @return ResponseInterface
     */
    public function emptyByKeyAction(): ResponseInterface
    {
        $key = $this->request->get('key');

        $this->cacheService->clear($key);

        return $this->response->redirect($this->url->get('cacheManager'));
    }
}