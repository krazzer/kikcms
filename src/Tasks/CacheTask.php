<?php declare(strict_types=1);

namespace KikCMS\Tasks;

use KikCMS\Classes\Phalcon\Task;
use KikCMS\Services\CacheService;
use KikCMS\Services\Cms\CmsService;

/**
 * @property CacheService $cacheService
 * @property CmsService $cmsService
 */
class CacheTask extends Task
{
    /**
     * Called by: php kikcms cache
     */
    public function mainAction()
    {
        echo "Possible actions: clear" . PHP_EOL;
    }

    /**
     * Called by: php kikcms cache clear
     */
    public function clearAction()
    {
        $url = $this->url->getBaseUri() . 'cache/clear/' . $this->cmsService->createSecurityToken();

        $response = $this->jsonService->getByUrl($url);

        if ( ! isset($response['success']) || ! $response['success']) {
            echo 'Cache clear failed!' . PHP_EOL;
        } else {
            echo 'Cache cleared succesfully!' . PHP_EOL;
        }
    }
}