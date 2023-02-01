<?php declare(strict_types=1);


namespace KikCMS\Controllers;


use DateTime;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Config\MenuConfig;
use Phalcon\Http\ResponseInterface;

class CacheController extends BaseCmsController
{
    /**
     * Display control panel for APCu cache
     */
    public function managerAction(): ResponseInterface
    {
        if( ! $this->permission->isAdmin()){
            throw new UnauthorizedException;
        }

        $cacheInfo = @apcu_cache_info() ?: [];

        $startTime = isset($cacheInfo['start_time']) ? (new DateTime())->setTimestamp($cacheInfo['start_time']) : new DateTime;

        return $this->view('cms/cacheManager', [
            'title'            => 'Cache beheer',
            'cacheInfo'        => $cacheInfo,
            'uptime'           => $startTime->diff(new DateTime()),
            'memorySize'       => $this->byteService->bytesToString((int) ($cacheInfo['mem_size'] ?? 0)),
            'cacheNodeMap'     => $this->cacheService->getCacheNodeMap(),
            'selectedMenuItem' => MenuConfig::MENU_ITEM_SETTINGS,
        ], 200);
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