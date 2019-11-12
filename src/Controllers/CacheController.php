<?php declare(strict_types=1);


namespace KikCMS\Controllers;


use DateTime;
use Exception;
use KikCMS\Config\MenuConfig;
use KikCMS\Services\CacheService;
use KikCMS\Services\Util\ByteService;
use Phalcon\Cache\Backend;
use Phalcon\Http\ResponseInterface;

/**
 * @property CacheService $cacheService
 * @property Backend $cache
 * @property ByteService $byteService
 */
class CacheController extends BaseCmsController
{
    /**
     * Display control panel for APCu cache
     */
    public function managerAction()
    {
        try{
            $cacheInfo = apcu_cache_info();
        } catch(Exception $e){
            $cacheInfo = [];
        }

        $startTime = isset($cacheInfo['start_time']) ? (new DateTime())->setTimestamp($cacheInfo['start_time']) : new DateTime;

        $this->view->title            = 'Cache beheer';
        $this->view->cacheInfo        = $cacheInfo;
        $this->view->uptime           = $startTime->diff(new DateTime());
        $this->view->memorySize       = $this->byteService->bytesToString((int) ($cacheInfo['mem_size'] ?? 0));
        $this->view->cacheNodeMap     = $this->cacheService->getCacheNodeMap();
        $this->view->selectedMenuItem = MenuConfig::MENU_ITEM_SETTINGS;

        $this->view->pick('cms/cacheManager');
    }

    /**
     * @return ResponseInterface
     */
    public function emptyByKeyAction(): ResponseInterface
    {
        $key = (string) $this->request->get('key');

        $this->cacheService->clear($key);

        return $this->response->redirect($this->url->get('cacheManager'));
    }
}