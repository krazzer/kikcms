<?php
declare(strict_types=1);

namespace unit\Tasks;

use Codeception\Test\Unit;
use KikCMS\Classes\Phalcon\Url;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\Util\JsonService;
use KikCMS\Tasks\CacheTask;

class CacheTaskTest extends Unit
{
    public function testClearAction()
    {
        $this->getCacheTask(['success' => true])->clearAction();
        $this->expectOutputRegex('/Cache cleared succesfully/');

        $this->getCacheTask(['success' => false])->clearAction();
        $this->expectOutputRegex('/Cache clear failed/');

        $this->getCacheTask(null)->clearAction();
        $this->expectOutputRegex('/Cache clear failed/');

        $this->getCacheTask([])->clearAction();
        $this->expectOutputRegex('/Cache clear failed/');
    }

    /**
     * @param mixed $return
     * @return CacheTask
     */
    private function getCacheTask($return): CacheTask
    {
        $cacheTask = new CacheTask();

        $url = $this->createMock(Url::class);
        $url->method('getBaseUri')->willReturn('');

        $cmsService = $this->createMock(CmsService::class);
        $cmsService->method('createSecurityToken')->willReturn('');

        $jsonService = $this->createMock(JsonService::class);
        $jsonService->method('getByUrl')->willReturn($return);

        $cacheTask->url         = $url;
        $cacheTask->cmsService  = $cmsService;
        $cacheTask->jsonService = $jsonService;

        return $cacheTask;
    }
}
