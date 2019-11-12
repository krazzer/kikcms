<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;
use KikCMS\Models\File;
use Phalcon\Mvc\Model\Query\Builder;

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
        $I->getDbService()->insert(File::class, ['id' => 1, 'name' => 'testfile', 'hash' => 'abc']);

        $I->amOnPage('/cms/file/1');
        $I->seeInCurrentUrl('/media/files/abc/testfile');
    }

    /**
     * @param string $folderName
     * @return Builder
     */
    private function _getTestFolderQuery(string $folderName = 'test'): Builder
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