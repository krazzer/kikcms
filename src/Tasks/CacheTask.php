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
        $username = $password = $baseUrl = null;

        foreach ($params as $i => $param){
            if(substr($param, 0, 10) == '--username'){
                $username = $params[$i+1];
            }

            if(substr($param, 0, 10) == '--password'){
                $password = $params[$i+1];
            }

            if(substr($param, 0, 5) == '--url'){
                $baseUrl = $params[$i+1];
            }
        }

        $url = ($baseUrl ?: $this->url->getBaseUri()) . 'cache/clear/' . $this->cmsService->createSecurityToken();

        $response = $this->jsonService->getByUrl($url, $username, $password);

        if ( ! $response || ! isset($response['success']) || ! $response['success']) {
            echo "\033[31mCache clear failed!\033[0m" . PHP_EOL;
        } else {
            echo 'Cache cleared succesfully!' . PHP_EOL;
        }
    }
}