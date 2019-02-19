<?php

namespace KikCMS\Classes\Finder;


use Exception;
use Helpers\TestHelper;
use KikCMS\Services\Finder\FinderFileService;
use Phalcon\Http\Request\File;
use PHPUnit\Framework\TestCase;

class FinderTest extends TestCase
{
    public function testUploadFiles()
    {
        $finder = new Finder((new FinderFilters())->setFolderId(1));

        $fileMock = $this->createMock(File::class);
        $fileMock->method('getRealType')->willReturn('image/jpeg');
        $fileMock->method('getExtension')->willReturn('jpg');
        $fileMock->method('getError')->willReturn(false);

        $fileServiceMock = $this->createMock(FinderFileService::class);
        $fileServiceMock->method('create')->willReturn(1);
        $fileServiceMock->method('overwrite')->willReturn(true);

        $fileServiceMockNoOverWrite = $this->createMock(FinderFileService::class);
        $fileServiceMockNoOverWrite->method('create')->willReturn(1);
        $fileServiceMockNoOverWrite->method('overwrite')->willReturn(false);

        $finder->translator = (new TestHelper)->getTranslator();
        $finder->finderFileService = $fileServiceMock;

        $files = [$fileMock, $fileMock];

        $result = $finder->uploadFiles($files);

        // test success
        $this->assertInstanceOf(UploadStatus::class, $result);

        $this->assertCount(0, $result->getErrors());

        // test overwrite success
        $files = [$fileMock];

        $this->assertEquals([25], $finder->uploadFiles($files, 25)->getFileIds());

        // test overwrite fail
        $finder->finderFileService = $fileServiceMockNoOverWrite;

        $result = $finder->uploadFiles($files, 25);

        $this->assertEquals([], $result->getFileIds());
        $this->assertCount(1, $result->getErrors());

        // test has error
        $fileMock = $this->createMock(File::class);
        $fileMock->method('getRealType')->willReturn('image/jpeg');
        $fileMock->method('getExtension')->willReturn('jpg');
        $fileMock->method('getError')->willReturn(true);

        $files = [$fileMock];

        $this->assertCount(1, $finder->uploadFiles($files)->getErrors());

        // test mimetype not allowed
        $fileMockInValidExt = $this->createMock(File::class);
        $fileMockInValidExt->method('getRealType')->willReturn('InvalidMimeType');
        $fileMockInValidExt->method('getExtension')->willReturn('InvalidExtension');
        $fileMockInValidExt->method('getError')->willReturn(false);

        $files = [$fileMockInValidExt];

        $this->assertCount(1, $finder->uploadFiles($files)->getErrors());

        // test exception
        $this->expectException(Exception::class);

        $files = [$fileMock, $fileMock];

        $finder->uploadFiles($files, true);
    }
}
