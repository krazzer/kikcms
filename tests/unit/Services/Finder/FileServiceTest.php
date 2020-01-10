<?php
declare(strict_types=1);

namespace Services\Finder;

use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Classes\ObjectStorage\FileStorage;
use KikCMS\Models\File;
use KikCMS\Services\Finder\FileService;

class FileServiceTest extends Unit
{
    public function testGetImageDimensions()
    {
        $fileService = new FileService('media', 'thumbs');
        $fileService->setDI($this->getDbDi());

        $fileStorage = $this->createMock(FileStorage::class);
        $fileStorage->method('getStorageDir')->willReturn('');

        $fileService->fileStorage = $fileStorage;

        $file = new File();
        $file->id = 1;
        $file->mimetype = '';
        $file->extension = '';

        // not an image
        $this->assertNull($fileService->getImageDimensions($file));

        // image, but file doesnt exist
        $file->mimetype = 'image/png';
        $file->extension = 'png';

        $this->assertNull($fileService->getImageDimensions($file));

        // image, but file doesnt exist
        $fileStorage = $this->createMock(FileStorage::class);
        $fileStorage->method('getStorageDir')->willReturn((new TestHelper)->getSitePath() . 'storage/');

        $fileService->fileStorage = $fileStorage;

        $this->assertEquals(1000, $fileService->getImageDimensions($file)[0]);
    }
}
