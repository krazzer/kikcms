<?php

namespace KikCMS\Services\Finder;


use Helpers\TestHelper;
use KikCMS\Models\File;
use KikCMS\Models\PageLanguage;
use KikCMS\ObjectLists\PageLanguageMap;
use PHPUnit\Framework\TestCase;

class FileRemoveServiceTest extends TestCase
{
    public function testGetDeleteErrorMessage()
    {
        $di = (new TestHelper)->getTestDi();

        $fileRemoveService = new FileRemoveService();
        $fileRemoveService->setDI($di);

        // file has key: can't delete
        $errorMessage = $fileRemoveService->getDeleteErrorMessage((new File)->setKey('k'), true, new PageLanguageMap);
        $this->assertStringContainsString('At least one of the selected files hasn\'t been removed', $errorMessage);

        // can't edit file
        $errorMessage = $fileRemoveService->getDeleteErrorMessage(new File, false, new PageLanguageMap);
        $this->assertStringContainsString('insuffient rights', $errorMessage);

        // no linked pages
        $this->assertNull($fileRemoveService->getDeleteErrorMessage(new File, true, new PageLanguageMap));

        // one linked page
        $pageLanguageMap = (new PageLanguageMap)->add((new PageLanguage)->setName('x'), 1);

        $errorMessage = $fileRemoveService->getDeleteErrorMessage((new File)->setName('x'), true, $pageLanguageMap);
        $this->assertStringContainsString("because it is used in the page 'x'", $errorMessage);

        // multiple linked pages
        $pageLanguageMap = (new PageLanguageMap)
            ->add((new PageLanguage)->setName('x'), 1)
            ->add((new PageLanguage)->setName('x2'), 2);

        $errorMessage = $fileRemoveService->getDeleteErrorMessage((new File)->setName('x'), true, $pageLanguageMap);
        $this->assertStringContainsString("because it is used in the following pages: x, x2", $errorMessage);
    }

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

        if ( ! file_exists($dir1)) {
            mkdir($dir1);
        }

        if ( ! file_exists($dir2)) {
            mkdir($dir2);
        }

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
