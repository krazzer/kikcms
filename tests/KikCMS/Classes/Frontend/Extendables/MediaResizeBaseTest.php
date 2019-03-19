<?php

namespace KikCMS\Classes\Frontend\Extendables;


use Phalcon\Image\Adapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaResizeBaseTest extends TestCase
{
    public function testCrop()
    {
        $mockBuilder = $this->getMockBuilder(MediaResizeBase::class);
        $mockBuilder->setMethods(['resize']);

        /** @var MockObject|MediaResizeBase $mediaResizeBaseMock */
        $mediaResizeBaseMock = $mockBuilder->getMock();

        // test inside bounds
        $imageMock = $this->getImageMock(50, 50);
        $imageMock->expects($this->never())->method('crop');

        $mediaResizeBaseMock->crop($imageMock, 100, 100);

        // test resize same ratio
        /** @var MockObject|Adapter $imageMock */
        $imageMock = $this->createMock(Adapter::class);
        $imageMock->method('getWidth')->willReturnOnConsecutiveCalls(150, 100);
        $imageMock->method('getHeight')->willReturnOnConsecutiveCalls(100);

        $imageMock->expects($this->once())->method('crop')->with(100, 100, 0, 0);

        $mediaResizeBaseMock->crop($imageMock, 100, 100);

        // test different ratio
        /** @var MockObject|Adapter $imageMock */
        $imageMock = $this->createMock(Adapter::class);
        $imageMock->method('getWidth')->willReturn(200);
        $imageMock->method('getHeight')->willReturn(100);

        $imageMock->expects($this->once())->method('crop')->with(100, 100, 50, 0);

        $mediaResizeBaseMock->crop($imageMock, 100, 100);
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
        $imageMock->expects($this->once())->method('resize')->with(100, 200);

        $mediaResizeBase->resize($imageMock, 100, 100);

        // test wide image
        $imageMock = $this->getImageMock(300, 150);
        $imageMock->expects($this->once())->method('resize')->with(200, 100);

        $mediaResizeBase->resize($imageMock, 100, 100);
    }

    /**
     * @param int $width
     * @param int $height
     * @return MockObject|Adapter
     */
    private function getImageMock(int $width, int $height): MockObject
    {
        $imageMock = $this->createMock(Adapter::class);

        $imageMock->method('getWidth')->willReturn($width);
        $imageMock->method('getHeight')->willReturn($height);

        return $imageMock;
    }
}
