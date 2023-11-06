<?php
declare(strict_types=1);

namespace unit\Services\Finder;

use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Models\File;
use KikCMS\Services\Finder\FileResizeService;
use KikCMS\Services\Finder\FileService;
use Phalcon\Config\Config;
use Phalcon\Image\Adapter\Imagick;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileResizeServiceTest extends TestCase
{
    public function testResizeWithinBoundaries()
    {
        $fileResizeService = new FileResizeService();

        $fileService = $this->createMock(FileService::class);
        $fileService->method('getFilePath')->willReturn('/some/filepath');

        $fileResizeService->fileService  = $fileService;
        $fileResizeService->imageHandler = $this->createImageHandlerMock(0, 0, false, false);

        // not an image
        $fileResizeService->resizeWithinBoundaries($this->createFileMock(false));

        // will resize
        $fileResizeService->config       = $this->createConfig(1000, 1000);
        $fileResizeService->imageHandler = $this->createImageHandlerMock(1500, 1500, true, true);

        $fileResizeService->resizeWithinBoundaries($this->createFileMock(true));

        // will not resize
        $fileResizeService->config       = $this->createConfig(1000, 1000);
        $fileResizeService->imageHandler = $this->createImageHandlerMock(900, 900, false, true);

        $fileResizeService->resizeWithinBoundaries($this->createFileMock(true));
    }

    /**
     * @param int $imageWidth
     * @param int $imageHeight
     * @param bool $willResize
     * @param bool $willSave
     * @return MockObject
     */
    private function createImageHandlerMock(int $imageWidth, int $imageHeight, bool $willResize, bool $willSave): MockObject
    {
        $image = $this->createMock(Imagick::class);
        $image->method('getWidth')->willReturn($imageWidth);
        $image->method('getHeight')->willReturn($imageHeight);

        $image->expects($willResize ? $this->once() : $this->never())->method('resize');
        $image->expects($willSave ? $this->once() : $this->never())->method('save');

        $imageHandler = $this->createMock(ImageHandler::class);
        $imageHandler->method('create')->willReturn($image);

        return $imageHandler;
    }

    /**
     * @param bool $isImage
     * @return MockObject|File
     */
    private function createFileMock(bool $isImage = true): MockObject
    {
        $file = $this->createMock(File::class);
        $file->method('isImage')->willReturn($isImage);

        return $file;
    }

    /**
     * @param int $maxWidth
     * @param int $maxHeight
     * @return Config
     */
    private function createConfig(int $maxWidth, int $maxHeight): Config
    {
        $config                    = new Config();
        $config->media             = new Config();
        $config->media->maxWidth   = $maxWidth;
        $config->media->maxHeight  = $maxHeight;
        $config->media->jpgQuality = 100;

        return $config;
    }
}
