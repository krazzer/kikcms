<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;
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

        $I->runShellCommand('php /opt/project/tests/TestSitePath/kikcms generate models');

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

        $I->runShellCommand('php /opt/project/tests/TestSitePath/kikcms generate model test_generate_test');

        $I->getDbService()->db->dropTable('test_generate_test');

        foreach (self::FILES as $file){
            $I->assertTrue(file_exists($file));
        }

        $this->_deleteFiles();
    }

    public function mainWorks(FunctionalTester $I)
    {
        $I->runShellCommand('php /opt/project/tests/TestSitePath/kikcms main');
        $I->seeInShellOutput('This is the default task and the default action');
    }

    public function updateNestedSetWorks(FunctionalTester $I)
    {
        $I->runShellCommand('php /opt/project/tests/TestSitePath/kikcms main updateNestedSet');
        $I->seeShellOutputMatches('//');
    }

    public function updateMissingFileHashesWorks(FunctionalTester $I)
    {
        $I->runShellCommand('php /opt/project/tests/TestSitePath/kikcms main updateMissingFileHashes');
        $I->seeShellOutputMatches('//');
    }

    public function cleanUpVendorWorks(FunctionalTester $I)
    {
        $I->runShellCommand('php /opt/project/tests/TestSitePath/kikcms main cleanUpVendor');
        $I->seeShellOutputMatches('//');
    }

    private function _deleteFiles()
    {
        foreach (self::FILES as $file){
            unlink($file);
        }
    }
}