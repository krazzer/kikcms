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
     * Called by: php kikcms cache clear
     * @param array $params [0 => username, 1 => password]
     */
    public function clearAction(array $params = [])
    {
        if(isset($params[0]) && substr($params[0], 0, 8) == 'https://'){
            $baseUrl = $params[0];
            unset($params[0]);
        } else {
            $baseUrl = null;
        }

        $username = $params[0] ?? null;
        $password = $params[1] ?? null;

        $url = ($baseUrl ?: $this->url->getBaseUri()) . 'cache/clear/' . $this->cmsService->createSecurityToken();

        $response = $this->jsonService->getByUrl($url, $username, $password);

        if ( ! $response || ! isset($response['success']) || ! $response['success']) {
            echo "\033[31mCache clear failed!\033[0m" . PHP_EOL;
        } else {
            echo 'Cache cleared succesfully!' . PHP_EOL;
        }
    }
}