<?php
declare(strict_types=1);

namespace Services\Finder;

use Exception;
use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Classes\Finder\UploadStatus;
use KikCMS\Classes\ObjectStorage\FileStorage;
use KikCMS\Models\File;
use KikCMS\Services\Finder\FileService;
use PHPUnit\Framework\MockObject\MockObject;

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

    public function testUploadFiles()
    {
        $fileService = new FileService('', '');

        $fileMock = $this->getFileMock('image/png', 'png');

        $fileServiceMock = $this->createMock(FileService::class);
        $fileServiceMock->method('create')->willReturn(1);
        $fileServiceMock->method('overwrite')->willReturn(true);

        $fileServiceMockNoOverWrite = $this->createMock(FileService::class);
        $fileServiceMockNoOverWrite->method('create')->willReturn(1);
        $fileServiceMockNoOverWrite->method('overwrite')->willReturn(false);

        $fileService->translator  = (new TestHelper)->getTranslator();
        $fileService->fileService = $fileServiceMock;

        $files = [$fileMock, $fileMock];

        $result = $fileService->uploadFiles($files);

        // test success
        $this->assertInstanceOf(UploadStatus::class, $result);

        $this->assertCount(0, $result->getErrors());

        // test overwrite success
        $files = [$fileMock];

        $this->assertEquals([25], $fileService->uploadFiles($files, 1, 25)->getFileIds());

        // test overwrite fail
        $fileService->fileService = $fileServiceMockNoOverWrite;

        $result = $fileService->uploadFiles($files, 1, 25);

        $this->assertEquals([], $result->getFileIds());
        $this->assertCount(1, $result->getErrors());

        // test has error
        $files = [$this->getFileMock('image/png', 'png', true)];

        $this->assertCount(1, $fileService->uploadFiles($files)->getErrors());

        // test mimetype not allowed
        $files = [$this->getFileMock('InvalidMimeType', 'InvalidExtension')];

        $this->assertCount(1, $fileService->uploadFiles($files)->getErrors());

        // test exception
        $this->expectException(Exception::class);

        $files = [$fileMock, $fileMock];

        $fileService->uploadFiles($files, 1, 1);
    }

    public function testMimeTypeAllowed()
    {
        $fileService = new FileService('', '');

        $this->assertTrue($fileService->mimeTypeAllowed($this->getFileMock('image/png', 'png')));
        $this->assertFalse($fileService->mimeTypeAllowed($this->getFileMock('InvalidMime', 'png')));
        $this->assertFalse($fileService->mimeTypeAllowed($this->getFileMock('image/png', 'InvalidExt')));
        $this->assertFalse($fileService->mimeTypeAllowed($this->getFileMock('image/png', 'jpg')));
    }

    /**
     * @param string $mimeType
     * @param string $extension
     * @param bool $error
     * @return MockObject|\Phalcon\Http\Request\File
     */
    private function getFileMock(string $mimeType, string $extension, bool $error = false): MockObject
    {
        $mock = $this->createMock(\Phalcon\Http\Request\File::class);
        $mock->method('getRealType')->willReturn($mimeType);
        $mock->method('getExtension')->willReturn($extension);
        $mock->method('getError')->willReturn($error);

        return $mock;
    }
}
