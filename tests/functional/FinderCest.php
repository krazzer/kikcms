<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;
use KikCMS\Models\File;
use KikCMS\Models\FilePermission;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Query\BuilderInterface;

class FinderCest
{
    public function _before(FunctionalTester $I)
    {
        $I->login();
    }

    public function createAndDeleteFolderWorks(FunctionalTester $I)
    {
        $folderId = $this->_createFolder($I, 'test');

        $I->assertNotNull($folderId);

        $I->canSeeResponseCodeIs(200);

        $I->sendAjaxPostRequest('/cms/finder/delete', [
            'renderableInstance' => 'Finder5dc515ba1b715',
            'renderableClass'    => 'KikCMS\Classes\Finder\Finder',
            'fileIds'            => [$folderId],
        ]);

        $I->assertNull($I->getDbService()->getValue($this->_getTestFolderQuery()));

        $I->canSeeResponseCodeIs(200);
    }

    public function editFileNameWorks(FunctionalTester $I)
    {
        $folderId = $this->_createFolder($I, 'test');

        $I->sendAjaxPostRequest('/cms/finder/editFileName', [
            'renderableInstance' => 'Finder5dc515ba1b715',
            'renderableClass'    => 'KikCMS\Classes\Finder\Finder',
            'fileName'           => 'newname',
            'fileId'             => $folderId,
        ]);

        $newFolderId = $this->_getTestFolderQuery('newname');

        $I->assertNotNull($newFolderId);
    }

    public function fileWorks(FunctionalTester $I)
    {
        $I->getDbService()->insert(File::class, ['id' => 1, 'name' => 'testfile', 'hash' => 'abc', 'extension' => 'png']);
        $I->getDbService()->insert(FilePermission::class, ['id' => 1, 'user_id' => 1, 'file_id' => 1, 'right' => 2]);

        $I->amOnPage('/cms/file/1');
        $I->seeInCurrentUrl('/media/files/abc/1-testfile');
    }

    public function keyWorks(FunctionalTester $I)
    {
        $I->getDbService()->insert(File::class, ['id' => 1, 'name' => 'testfile', 'hash' => 'abc', 'extension' => 'png', 'key' => 'test']);
        $I->getDbService()->insert(FilePermission::class, ['id' => 1, 'user_id' => 1, 'file_id' => 1, 'right' => 2]);

        $I->amOnPage('/cms/file/key/test');
        $I->seeInCurrentUrl('/media/files/abc/1-testfile');
    }

    public function openFolderWorks(FunctionalTester $I)
    {
        $folderId = $this->_createFolder($I, 'test');

        $I->sendAjaxPostRequest('/cms/finder/openFolder', [
            'renderableInstance' => 'Finder5dc515ba1b715',
            'renderableClass'    => 'KikCMS\Classes\Finder\Finder',
            'folderId'           => $folderId,
        ]);

        $I->see('{"files":');

        $I->canSeeResponseCodeIs(200);
    }

    public function pasteWorks(FunctionalTester $I)
    {
        $folderId = $this->_createFolder($I, 'test');

        $I->getDbService()->insert(File::class, [
            'id' => 1, 'name' => 'testfile', 'hash' => 'abc', 'extension' => 'png', 'folder_id' => $folderId
        ]);

        $I->sendAjaxPostRequest('/cms/finder/paste', [
            'renderableInstance' => 'Finder5dc515ba1b715',
            'renderableClass'    => 'KikCMS\Classes\Finder\Finder',
            'fileIds'            => [1],
            'folderId'           => $folderId,
        ]);

        $I->sendAjaxPostRequest('/cms/finder/openFolder', [
            'renderableInstance' => 'Finder5dc515ba1b715',
            'renderableClass'    => 'KikCMS\Classes\Finder\Finder',
            'folderId'           => $folderId,
        ]);

        $I->assertStringContainsString('<div class="file file-1"', json_decode($I->grabPageSource())->files);
        $I->canSeeResponseCodeIs(200);
    }

    public function searchWorks(FunctionalTester $I)
    {
        $I->getDbService()->insert(File::class, ['id' => 1, 'name' => 'testfile', 'hash' => 'abc', 'extension' => 'png']);
        $I->getDbService()->insert(File::class, ['id' => 2, 'name' => 'searchfile', 'hash' => 'abc', 'extension' => 'png']);

        $I->getDbService()->insert(FilePermission::class, ['id' => 1, 'user_id' => 1, 'file_id' => 1, 'right' => 2]);
        $I->getDbService()->insert(FilePermission::class, ['id' => 2, 'user_id' => 1, 'file_id' => 2, 'right' => 2]);

        $I->sendAjaxPostRequest('/cms/finder/search', [
            'renderableInstance' => 'Finder5dc515ba1b715',
            'renderableClass'    => 'KikCMS\Classes\Finder\Finder',
            'search'             => 'searchfile',
        ]);

        $filesHtml = json_decode($I->grabPageSource())->files;

        $I->assertStringContainsString('<div class="file file-2"', $filesHtml);
        $I->assertStringNotContainsString('<div class="file file-1"', $filesHtml);

        $I->canSeeResponseCodeIs(200);
    }

    public function urlWorks(FunctionalTester $I)
    {
        $I->getDbService()->insert(File::class, ['id' => 1, 'name' => 'testfile.png', 'hash' => 'abc', 'extension' => 'png']);

        $I->amOnPage('/cms/file/url/1');

        $url = json_decode($I->grabPageSource())->url;

        $I->assertStringContainsString('https://kikcmstest.dev/media/files/1-testfile', $url);

        $I->canSeeResponseCodeIs(200);
    }

    /**
     * @param string $folderName
     * @return BuilderInterface
     */
    private function _getTestFolderQuery(string $folderName = 'test'): BuilderInterface
    {
        return (new Builder)
            ->columns('id')
            ->from(File::class)
            ->where('name = :name:', ['name' => $folderName]);
    }

    /**
     * @param FunctionalTester $I
     * @param string $folderName
     * @return int
     */
    private function _createFolder(FunctionalTester $I, string $folderName): int
    {
        $I->sendAjaxPostRequest('/cms/finder/createFolder', [
            'renderableInstance' => 'Finder5dc515ba1b715',
            'renderableClass'    => 'KikCMS\Classes\Finder\Finder',
            'folderName'         => $folderName,
        ]);

        $query = $this->_getTestFolderQuery();

        return (int) $I->getDbService()->getValue($query);
    }
}