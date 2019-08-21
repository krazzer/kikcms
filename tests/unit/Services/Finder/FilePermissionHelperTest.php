<?php
declare(strict_types=1);

namespace Services\Finder;


use Codeception\Test\Unit;
use Helpers\TestHelper;
use KikCMS\Classes\Permission;
use KikCMS\Config\FinderConfig;
use KikCMS\Models\File;
use KikCMS\Models\FilePermission;
use KikCMS\Models\User;
use KikCMS\Services\Finder\FilePermissionHelper;


class FilePermissionHelperTest extends Unit
{
    public function testGetPermissionTable()
    {
        $testHelper = new TestHelper();

        $filePermissionHelper = new FilePermissionHelper();
        $filePermissionHelper->setDI($testHelper->getTestDi());

        $filePermissionHelper->dbService->truncate(File::class);
        $filePermissionHelper->dbService->truncate(FilePermission::class);

        // empty result
        $result   = $filePermissionHelper->getPermissionTable([1]);
        $expected = [
            'developer' => ['disabled' => 1],
            'admin'     => ['disabled' => 0],
            'user'      => ['disabled' => 0],
            'client'    => ['disabled' => 0],
            'visitor'   => ['disabled' => 0],
        ];

        $this->assertEquals($expected, $result);

        // dev has write access
        $filePermissionHelper->dbService->insert(File::class, [File::FIELD_ID => 1]);
        $filePermissionHelper->dbService->insert(FilePermission::class, [
            FilePermission::FIELD_ROLE    => Permission::DEVELOPER,
            FilePermission::FIELD_RIGHT   => FinderConfig::RIGHT_WRITE,
            FilePermission::FIELD_FILE_ID => 1,
        ]);

        $result   = $filePermissionHelper->getPermissionTable([1]);
        $expected = [
            'developer' => ['disabled' => true, 'read' => 0, 'write' => 1],
            'admin'     => ['disabled' => 0],
            'user'      => ['disabled' => 0],
            'client'    => ['disabled' => 0],
            'visitor'   => ['disabled' => 0],
        ];

        $this->assertEquals($expected, $result);

        // user has write access
        $filePermissionHelper->dbService->truncate(File::class);
        $filePermissionHelper->dbService->truncate(FilePermission::class);
        $filePermissionHelper->dbService->truncate(User::class);

        $filePermissionHelper->dbService->insert(File::class, [File::FIELD_ID => 1]);
        $filePermissionHelper->dbService->insert(User::class, ['id' => 1, 'role' => 'admin', 'email' => 'test@test.com']);
        $filePermissionHelper->dbService->insert(FilePermission::class, ['right' => 2, 'user_id' => 1, 'file_id' => 1]);

        $result   = $filePermissionHelper->getPermissionTable([1]);
        $expected = [
            'developer' => ['disabled' => 1],
            'admin'     => ['disabled' => 0],
            'user'      => ['disabled' => 0],
            'client'    => ['disabled' => 0],
            'visitor'   => ['disabled' => 0],
            1           => ['read' => 0, 'write' => 1],
        ];

        $this->assertEquals($expected, $result);

        $filePermissionHelper->dbService->truncate(File::class);
        $filePermissionHelper->dbService->truncate(FilePermission::class);
        $filePermissionHelper->dbService->truncate(User::class);
    }
}
