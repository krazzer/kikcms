<?php

namespace unit\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use Phalcon\Image\Adapter\AbstractAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaResizeBaseTest extends TestCase
{
    public function testCrop()
    {
        $mediaResizeBase = new MediaResizeBase();

        // test inside bounds
        $imageMock = $this->getImageMock(50, 50);
        $imageMock->expects($this->never())->method('resize');
        $imageMock->expects($this->never())->method('crop');
        $mediaResizeBase->crop($imageMock, 100, 100);

        // test resize same ratio, needs resize
        $imageMock = $this->getImageMock(150, 100);
        $imageMock->expects($this->once())->method('resize')->with(75, 50);
        $imageMock->expects($this->once())->method('crop')->with(75, 50, 0, 0);
        $mediaResizeBase->crop($imageMock, 75, 50);

        // test different ratio
        $imageMock = $this->getImageMock(200, 100);
        $imageMock->expects($this->never())->method('resize');
        $imageMock->expects($this->once())->method('crop')->with(100, 100, 50, 0);
        $mediaResizeBase->crop($imageMock, 100, 100);

        // test different ratio, and resize
        $imageMock = $this->getImageMock(200, 500);
        $imageMock->expects($this->once())->method('resize')->with(100, 250);
        $imageMock->expects($this->once())->method('crop')->with(100, 100, 0, 75);
        $mediaResizeBase->crop($imageMock, 100, 100);
    }

    public function testResize()
    {
        $mediaResizeBase = new MediaResizeBase();

        // test inside bounds
        $imageMock = $this->getImageMock(50, 50);
        $imageMock->expects($this->never())->method('resize');
        $mediaResizeBase->resize($imageMock, 100, 100);

        // test resize same ratio
        $imageMock = $this->getImageMock(150, 150);
        $imageMock->expects($this->once())->method('resize')->with(100, 100);
        $mediaResizeBase->resize($imageMock, 100, 100);

        // test tall image
        $imageMock = $this->getImageMock(150, 300);
        $imageMock->expects($this->once())->method('resize')->with(50, 100);
        $mediaResizeBase->resize($imageMock, 100, 100);

        // test wide image
        $imageMock = $this->getImageMock(300, 150);
        $imageMock->expects($this->once())->method('resize')->with(100, 50);
        $mediaResizeBase->resize($imageMock, 100, 100);
    }

    /**
     * @param int $width
     * @param int $height
     * @return MockObject|AbstractAdapter
     */
    private function getImageMock(int $width, int $height): MockObject
    {
        $imageMock = $this->createMock(AbstractAdapter::class);

        $imageMock->method('getWidth')->willReturn($width);
        $imageMock->method('getHeight')->willReturn($height);

        return $imageMock;
    }
}
