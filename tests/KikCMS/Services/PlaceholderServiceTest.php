<?php

namespace KikCMS\Services;


use Helpers\TestHelper;
use KikCMS\Models\FinderFile;
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
            [[[fileThumbUrl:1:default:0]]]
            [[[fileThumbUrl:1:default:1]]]
            [[[fileUrl:1:0]]]
            [[[fileUrl:1:1]]]
        ";

        $expectedResult = "
            /media/thumbs/default/1.png
            /media/thumbs/default/hash.png
            /media/files/1.png
            /media/files/hash.png
        ";

        $placeholderService->db->delete(FinderFile::TABLE);
        $placeholderService->dbService->insert(FinderFile::class, ['id' => 1, 'hash' => 'hash', 'extension' => 'png']);

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
        $placeholderService->db->delete(FinderFile::TABLE);
    }
}
