<?php
declare(strict_types=1);

namespace Services\Cms;

use Helpers\Unit;
use KikCMS\Services\Cms\CmsService;
use Phalcon\Config;
use Phalcon\Http\Request;

class CmsServiceTest extends Unit
{
    public function testGetBaseUri()
    {
        // baseUri set in config
        $baseUri = $this->getCmsServiceForGetBaseUri('https://configbaseuri.com')->getBaseUri();
        $this->assertEquals('https://configbaseuri.com', $baseUri);

        // baseUri not set in config, but request
        $baseUri = $this->getCmsServiceForGetBaseUri(null, 'someurl.com')->getBaseUri();
        $this->assertEquals('https://someurl.com/', $baseUri);

        // baseUri not set in config, no request, but domain in path on server
        $baseUri = $this->getCmsServiceForGetBaseUri(null, null, 'some/path/on/somedomain.com/the/server')->getBaseUri();
        $this->assertEquals('https://somedomain.com/', $baseUri);

        // baseUri not set in config, no request, no domain in server path
        $this->assertNull($this->getCmsServiceForGetBaseUri()->getBaseUri());
    }

    /**
     * @param null $configBaseUri
     * @param null $requestServerDomain
     * @param string $serverPath
     * @return CmsService
     */
    private function getCmsServiceForGetBaseUri($configBaseUri = null, $requestServerDomain = null, string $serverPath = ''): CmsService
    {
        $cmsService = new CmsService();

        $cmsService->config = new Config();
        $cmsService->config->application = new Config();
        $cmsService->config->application->baseUri = $configBaseUri;

        $request = $this->createMock(Request::class);
        $request->method('getServer')->willReturn($requestServerDomain);

        $cmsService->request = $request;

        $cmsService->config->application->path = $serverPath;

        return $cmsService;
    }
}
