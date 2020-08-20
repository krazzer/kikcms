<?php
declare(strict_types=1);

namespace unit\Services\Finder;

use Helpers\Unit;
use KikCMS\Classes\Finder\FinderFilters;
use KikCMS\Models\File;
use KikCMS\Models\Folder;
use KikCMS\Models\User;
use KikCMS\Services\Finder\FilePermissionService;
use KikCMS\Services\Finder\FinderService;
use KikCMS\Services\UserService;
use Phalcon\Session\Adapter;

class FinderServiceTest extends Unit
{
    public function testSetStartingFolder()
    {
        // folder is set in session, but doesn't exist
        $filters = new FinderFilters();
        $this->getFinderService(1)->setStartingFolder($filters);
        $this->assertNull($filters->getFolderId());

        // folder is set in session, and exist
        $filters = new FinderFilters();

        $finderService = $this->getFinderService(1);
        $finderService->dbService->insert(File::class, ['id' => 1, 'is_folder' => 1]);
        $finderService->setStartingFolder($filters);

        $this->assertEquals(1, $filters->getFolderId());

        // can't read
        $filters = new FinderFilters();
        $this->assertNull($this->getFinderService(null, false)->setStartingFolder($filters));
        $this->assertNull($filters->getFolderId());

        // no user folder
        $filters = new FinderFilters();
        $this->assertNull($this->getFinderService(null, true, null)->setStartingFolder($filters));
        $this->assertNull($filters->getFolderId());

        // user folder
        $filters = new FinderFilters();
        $this->assertNull($this->getFinderService(null, false, 2)->setStartingFolder($filters));
        $this->assertEquals(2, $filters->getFolderId());
    }

    /**
     * @param null $sessionFolderId
     * @param bool $canReadId
     * @param null $userFolderId
     * @return FinderService
     */
    private function getFinderService($sessionFolderId = null, $canReadId = true, $userFolderId = null): FinderService
    {
        $finderService = new FinderService();
        $finderService->setDI($this->getDbDi());

        $user = new User;

        if ($userFolderId) {
            $folder     = new Folder;
            $folder->id = $userFolderId;

            $user->folder = $folder;
        }

        $userService = $this->createMock(UserService::class);
        $userService->method('getUser')->willReturn($user);

        $session = $this->createMock(Adapter::class);
        $session->method('__get')->willReturn($sessionFolderId);

        $filePermissionService = $this->createMock(FilePermissionService::class);
        $filePermissionService->method('canReadId')->willReturn($canReadId);

        $finderService->filePermissionService = $filePermissionService;
        $finderService->userService           = $userService;
        $finderService->session               = $session;

        return $finderService;
    }
}
