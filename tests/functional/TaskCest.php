<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;
use KikCMS\Tasks\GenerateTask;
use KikCMS\Tasks\MainTask;
use KikCMS\Tasks\UrlTask;
use Phalcon\Db\Column;

class TaskCest
{
    const FILES = [
        '/opt/project/tests/TestSitePath/app/Objects/GenerateTest/GenerateTest.php',
        '/opt/project/tests/TestSitePath/app/Objects/GenerateTest/GenerateTestForm.php',
        '/opt/project/tests/TestSitePath/app/Objects/GenerateTest/GenerateTestList.php',
        '/opt/project/tests/TestSitePath/app/Objects/GenerateTest/GenerateTestMap.php',
        '/opt/project/tests/TestSitePath/app/Objects/GenerateTest/GenerateTests.php',
        '/opt/project/tests/TestSitePath/app/Objects/GenerateTest/GenerateTestService.php',
    ];

    public function modelsWorks(FunctionalTester $I)
    {
        $I->getDbService()->db->dropTable('test_generate_test');
        $I->getDbService()->db->createTable('test_generate_test', '', [
            'columns' => [new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11])],
        ]);

        $di = $I->getApplication()->getDI();

        $generateTask = new GenerateTask();
        $generateTask->setDI($di);

        $generateTask->modelsAction();

        $I->getDbService()->db->dropTable('test_generate_test');

        foreach (self::FILES as $file){
            $I->assertTrue(file_exists($file));
        }

        $this->_deleteFiles();
    }

    public function modelWorks(FunctionalTester $I)
    {
        $I->getDbService()->db->dropTable('test_generate_test');
        $I->getDbService()->db->createTable('test_generate_test', '', [
            'columns' => [new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11])],
        ]);

        $di = $I->getApplication()->getDI();

        $generateTask = new GenerateTask();
        $generateTask->setDI($di);

        $generateTask->modelAction(['test_generate_test']);

        $I->getDbService()->db->dropTable('test_generate_test');

        foreach (self::FILES as $file){
            $I->assertTrue(file_exists($file));
        }

        $this->_deleteFiles();
    }

    public function mainWorks(FunctionalTester $I)
    {
        $di = $I->getApplication()->getDI();

        $mainTask = new MainTask();
        $mainTask->setDI($di);

        $mainTask->mainAction();
    }

    public function updateNestedSetWorks(FunctionalTester $I)
    {
        $di = $I->getApplication()->getDI();

        $mainTask = new MainTask();
        $mainTask->setDI($di);

        $mainTask->updateNestedSetAction();
    }

    public function updateMissingFileHashesWorks(FunctionalTester $I)
    {
        $di = $I->getApplication()->getDI();

        $mainTask = new MainTask();
        $mainTask->setDI($di);

        $mainTask->updateMissingFileHashesAction();
    }

    public function createUrlsWorks(FunctionalTester $I)
    {
        $I->getDbService()->query('UPDATE cms_page_language SET slug = NULL WHERE id = 9');

        $di = $I->getApplication()->getDI();

        $urlTask = new UrlTask();
        $urlTask->setDI($di);

        $urlTask->createUrlsAction();
    }

    private function _deleteFiles()
    {
        foreach (self::FILES as $file){
            unlink($file);
        }

        rmdir(dirname(self::FILES[0]));
    }
}