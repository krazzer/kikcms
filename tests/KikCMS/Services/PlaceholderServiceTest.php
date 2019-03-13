<?php

namespace KikCMS\Services;


use Helpers\TestHelper;
use KikCMS\Models\File;
use PHPUnit\Framework\TestCase;

class PlaceholderServiceTest extends TestCase
{
    public function testReplaceAll()
    {
        $di = (new TestHelper)->getTestDi();

        $placeholderService = new PlaceholderService();
        $placeholderService->setDI($di);

        $content        = 'content';
        $expectedResult = 'content';

        // test no replace
        $this->assertEquals($expectedResult, $placeholderService->replaceAll($content));

        // test with replace
        $content = "
            [[[fileThumbUrl:1:default:public]]]
            [[[fileThumbUrl:1:default:private]]]
            [[[fileUrl:1:public]]]
            [[[fileUrl:1:private]]]
        ";

        $expectedResult = "
            /media/thumbs/default/1.png
            /media/thumbs/default/hash.png
            /media/files/1.png
            /media/files/hash.png
        ";

        $placeholderService->db->delete(File::TABLE);
        $placeholderService->dbService->insert(File::class, ['id' => 1, 'hash' => 'hash', 'extension' => 'png']);

        $this->assertEquals($expectedResult, $placeholderService->replaceAll($content));

        $createdFiles = [
            SITE_PATH . 'public_html/media/files/1.png',
            SITE_PATH . 'public_html/media/files/hash.png',
            SITE_PATH . 'public_html/media/thumbs/default/1.png',
            SITE_PATH . 'public_html/media/thumbs/default/hash.png',
        ];

        foreach ($createdFiles as $file){
            $this->assertFileExists($file);
            unlink($file);
        }

        // clean up db
        $placeholderService->db->delete(File::TABLE);
    }
}
