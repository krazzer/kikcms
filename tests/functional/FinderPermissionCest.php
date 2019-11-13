<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;
use KikCMS\Models\File;

class FinderPermissionCest
{
    public function _before(FunctionalTester $I)
    {
        $I->login();
    }

    /**
     * @param FunctionalTester $I
     */
    public function getWorks(FunctionalTester $I)
    {
        $I->getDbService()->insert(File::class, ['id' => 1, 'name' => 'testfile', 'hash' => 'abc']);
        $I->getDbService()->insert(File::class, ['id' => 2, 'name' => 'testfile2', 'hash' => 'abc']);

        $I->sendAjaxPostRequest('/cms/finder/permission/get', [
            'fileIds' => [1],
        ]);

        $I->see('"title":"testfile"');

        $I->sendAjaxPostRequest('/cms/finder/permission/get', [
            'fileIds' => [1, 2],
        ]);

        $I->see('{"title":"2 files"');
    }

    /**
     * @param FunctionalTester $I
     */
    public function updateWorks(FunctionalTester $I)
    {
        $I->getDbService()->insert(File::class, ['id' => 1, 'name' => 'testfile', 'hash' => 'abc']);

        $I->sendAjaxPostRequest('/cms/finder/permission/update', [
            'permission' => [
                1       => ['read' => 1], ['write' => 1],
                'admin' => ['read' => 1], ['write' => 1],
            ],
            'fileIds'    => [1],
            'recursive'  => 1,
        ]);

        $I->see('{"success":true}');
    }
}