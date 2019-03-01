<?php

namespace KikCMS\Classes\Finder;


use Exception;
use Helpers\TestHelper;
use KikCMS\Services\Finder\FinderFileService;
use Phalcon\Http\Request\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FinderTest extends TestCase
{
    public function testUploadFiles()
    {
        $finder = new Finder((new FinderFilters())->setFolderId(1));

        $fileMock = $this->getFileMock('image/png', 'png');

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
        $files = [$this->getFileMock('image/png', 'png', true)];

        $this->assertCount(1, $finder->uploadFiles($files)->getErrors());

        // test mimetype not allowed
        $files = [$this->getFileMock('InvalidMimeType', 'InvalidExtension')];

        $this->assertCount(1, $finder->uploadFiles($files)->getErrors());

        // test exception
        $this->expectException(Exception::class);

        $files = [$fileMock, $fileMock];

        $finder->uploadFiles($files, true);
    }

    public function testMimeTypeAllowed()
    {
        $finder = new Finder();

        $this->assertTrue($finder->mimeTypeAllowed($this->getFileMock('image/png', 'png')));
        $this->assertFalse($finder->mimeTypeAllowed($this->getFileMock('InvalidMime', 'png')));
        $this->assertFalse($finder->mimeTypeAllowed($this->getFileMock('image/png', 'InvalidExt')));
        $this->assertFalse($finder->mimeTypeAllowed($this->getFileMock('image/png', 'jpg')));
    }

    /**
     * @param string $mimeType
     * @param string $extension
     * @param bool $error
     * @return MockObject|File
     */
    private function getFileMock(string $mimeType, string $extension, bool $error = false): MockObject
    {
        $mock = $this->createMock(File::class);
        $mock->method('getRealType')->willReturn($mimeType);
        $mock->method('getExtension')->willReturn($extension);
        $mock->method('getError')->willReturn($error);

        return $mock;
    }
}
