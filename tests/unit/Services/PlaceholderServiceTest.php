<?php

namespace unit\Services;


use Helpers\TestHelper;
use KikCMS\Classes\Phalcon\IniConfig;
use KikCMS\Models\File;
use KikCMS\Services\PlaceholderService;
use PHPUnit\Framework\TestCase;

class PlaceholderServiceTest extends TestCase
{
    public function testReplaceAll()
    {
        $di       = (new TestHelper)->getTestDi();
        $sitePath = (new TestHelper)->getSitePath();

        $placeholderService = new PlaceholderService();

        $configStub = $this->createMock(IniConfig::class);
        $configStub->method('isDev')->willReturn(false);

        $placeholderService->config = $configStub;

        $placeholderService->setDI($di);

        $content        = 'content';
        $expectedResult = 'content';

        // test no replace
        $this->assertEquals($expectedResult, $placeholderService->replaceAll($content));

        // test with replace
        $content = "
            [[[fileThumbUrl.1.default.public]]]
            [[[fileThumbUrl.1.default.private]]]
            [[[fileUrl.1.public]]]
            [[[fileUrl.1.private]]]
        ";

        $expectedResult = "
            /media/thumbs/default/1.png
            /media/thumbs/default/hash.png
            /media/files/1-test.png
            /media/files/hash/1-test.png
        ";

        $placeholderService->db->delete(File::TABLE);
        $placeholderService->dbService->insert(File::class, ['id' => 1, 'name' => 'test.png', 'hash' => 'hash', 'extension' => 'png']);

        $this->assertEquals($expectedResult, $placeholderService->replaceAll($content));

        $createdFiles = [
            $sitePath . 'public_html/media/files/1-test.png',
            $sitePath . 'public_html/media/files/hash/1-test.png',
            $sitePath . 'public_html/media/thumbs/default/1.png',
            $sitePath . 'public_html/media/thumbs/default/hash.png',
        ];

        foreach ($createdFiles as $file) {
            $this->assertFileExists($file);
            unlink($file);
        }

        // clean up db
        $placeholderService->db->delete(File::TABLE);
    }
}
