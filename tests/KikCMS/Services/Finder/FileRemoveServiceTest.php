<?php

namespace KikCMS\Services\Finder;


use Helpers\TestHelper;
use KikCMS\Models\File;
use PHPUnit\Framework\TestCase;

class FileRemoveServiceTest extends TestCase
{
    public function testRemoveThumbNails()
    {
        $di       = (new TestHelper)->getTestDi();
        $sitePath = (new TestHelper)->getSitePath();

        $fileRemoveService = new FileRemoveService();
        $fileRemoveService->setDI($di);

        $file = new File();
        $file->id = 1;
        $file->extension = 'png';
        $file->hash = 'x';

        $dir1 = $sitePath . 'public_html/media/thumbs/dir1/';
        $dir2 = $sitePath . 'public_html/media/thumbs/dir2/';

        $file1 = $dir1 . '1.png';
        $file2 = $dir2 . '1.png';

        mkdir($dir1);
        mkdir($dir2);

        copy($sitePath . 'storage/media/1.png', $file1);
        copy($sitePath . 'storage/media/1.png', $file2);

        $fileRemoveService->removeThumbNails($file);

        $this->assertFileNotExists($file1);
        $this->assertFileNotExists($file2);

        // clean up
        if (file_exists($file1)) {
            unlink($file1);
        }

        if (file_exists($file2)) {
            unlink($file2);
        }

        rmdir($dir1);
        rmdir($dir2);
    }
}
