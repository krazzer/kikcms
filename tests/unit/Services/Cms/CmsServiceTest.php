<?php
declare(strict_types=1);

namespace unit\Services\Cms;

use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Classes\DataTable\SubDataTableNewIdsCache;
use KikCMS\Models\Page;
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

    public function testCleanUpDiskCache()
    {
        $cmsService = new CmsService();
        $cmsService->setDI($this->getDbDi());

        $subDataTableNewIdsCache = new SubDataTableNewIdsCache();
        $subDataTableNewIdsCache->setModel(Page::class);
        $subDataTableNewIdsCache->setColumn('parent_id');
        $subDataTableNewIdsCache->setIds([1]);

        $cmsService->dbService->insert(Page::class, ['id' => 1, 'type' => 'page', 'parent_id' => 0]);

        $file = (new TestHelper)->getSitePath() . 'storage/keyvalue/dataTableTestSubDataTableNewIdsCache';

        file_put_contents($file, json_encode(serialize($subDataTableNewIdsCache)));

        // too new won't delete
        $cmsService->cleanUpDiskCache();

        $this->assertFileExists($file);

        // do delete
        touch($file, time() + (3600 * 48));
        $cmsService->cleanUpDiskCache();

        $this->assertNull(Page::getById(1));

        // class doesnt exist
        $subDataTableNewIdsCache = new SubDataTableNewIdsCache();
        $subDataTableNewIdsCache->setModel('FakeClass');
        $subDataTableNewIdsCache->setColumn('id');
        $subDataTableNewIdsCache->setIds([1]);

        $file = (new TestHelper)->getSitePath() . 'storage/keyvalue/dataTableTestSubDataTableNewIdsCache';

        file_put_contents($file, json_encode(serialize($subDataTableNewIdsCache)));
        touch($file, time() + (3600 * 48));

        $cmsService->cleanUpDiskCache();

        $this->assertFileNotExists($file);
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
