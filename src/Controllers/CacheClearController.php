<?php


namespace KikCMS\Controllers;


use KikCMS\Services\CacheService;
use KikCMS\Services\Cms\CmsService;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Controller;

/**
 * Controller that can be reached externally, using a security token.
 * This can be useful if the cache cannot be cleared from the CLI
 *
 * @property CmsService $cmsService
 * @property CacheService $cacheService
 */
class CacheClearController extends Controller
{
    /**
     * @param string $token
     * @return ResponseInterface
     */
    public function clearAction(string $token): ResponseInterface
    {
        $this->cmsService->checkSecurityToken($token);
        $this->cacheService->clear();

        return $this->response->setJsonContent(['success' => true]);
    }
}