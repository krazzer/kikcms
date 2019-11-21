<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;
use Phalcon\Db\Column;

class GenerateTaskCest
{
    const FILES = [
        __DIR__ . '/../TestSitePath/app/Models/GenerateTest.php',
        __DIR__ . '/../TestSitePath/app/Forms/GenerateTestForm.php',
        __DIR__ . '/../TestSitePath/app/ObjectList/GenerateTestList.php',
        __DIR__ . '/../TestSitePath/app/ObjectList/GenerateTestMap.php',
        __DIR__ . '/../TestSitePath/app/DataTables/GenerateTests.php',
        __DIR__ . '/../TestSitePath/app/Services/GenerateTestService.php',
    ];

    public function modelsWorks(FunctionalTester $I)
    {
        $I->getDbService()->db->dropTable('test_generate_test');
        $I->getDbService()->db->createTable('test_generate_test', null, [
            'columns' => [new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11])],
        ]);

        $I->runShellCommand('php tests/TestSitePath/kikcms generate models');

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

        $I->runShellCommand('php tests/TestSitePath/kikcms generate model test_generate_test');

        $I->getDbService()->db->dropTable('test_generate_test');

        foreach (self::FILES as $file){
            $I->assertTrue(file_exists($file));
        }

        $this->_deleteFiles();
    }

    private function _deleteFiles()
    {
        foreach (self::FILES as $file){
            unlink($file);
        }
    }
}