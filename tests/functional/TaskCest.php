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
        '/opt/project/tests/TestSitePath/app/Models/GenerateTest.php',
        '/opt/project/tests/TestSitePath/app/Forms/GenerateTestForm.php',
        '/opt/project/tests/TestSitePath/app/ObjectList/GenerateTestList.php',
        '/opt/project/tests/TestSitePath/app/ObjectList/GenerateTestMap.php',
        '/opt/project/tests/TestSitePath/app/DataTables/GenerateTests.php',
        '/opt/project/tests/TestSitePath/app/Services/GenerateTestService.php',
    ];

    public function modelsWorks(FunctionalTester $I)
    {
        $I->getDbService()->db->dropTable('test_generate_test');
        $I->getDbService()->db->createTable('test_generate_test', null, [
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
        $I->getDbService()->db->createTable('test_generate_test', null, [
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

    public function cleanUpVendorWorks(FunctionalTester $I)
    {
        $di = $I->getApplication()->getDI();

        $mainTask = new MainTask();
        $mainTask->setDI($di);

        $mainTask->cleanUpVendorAction();
    }

    public function createUrlsWorks(FunctionalTester $I)
    {
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
    }
}