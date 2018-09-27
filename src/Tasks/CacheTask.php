<?php

use KikCMS\Services\CacheService;
use KikCMS\Services\Cms\CmsService;
use Phalcon\Cli\Task;

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

        $response = json_decode(file_get_contents($url), true);

        if ( ! isset($response['success']) || ! $response['success']) {
            echo 'Cache clear failed!' . PHP_EOL;
        }
    }
}