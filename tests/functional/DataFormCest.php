<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;
use Phalcon\Mvc\Model\Query\Builder;
use Website\Models\DataTableTest;
use Website\Models\TestPerson;

class DataFormCest
{
    public function renderWorks(FunctionalTester $I)
    {
        //TestUserPass
        $I->amOnPage('/test/personform');
        $I->seeElement('#webFormId_WebsiteFormsTestPersonForm');

        $I->submitForm('#webFormId_WebsiteFormsTestPersonForm form', [
            'name'    => 'test',
            'created' => '30-10-2020',
        ]);

        $persons = $I->getDbService()->getObjects((new Builder)->from(TestPerson::class));

        $I->assertCount(1, $persons);
    }

    public function allFieldTypeWorks(FunctionalTester $I)
    {
        $I->getApplication()->acl->setCurrentRole('developer');

        $I->amOnPage('/test/datatableform');
        $I->makeHtmlSnapshot(1);
        $I->submitForm('#webFormId_WebsiteFormsDataTableTestForm form', [
            'text'            => 'testtext',
            'file_id'         => 1,
            'checkbox'        => 1,
            'select'          => 1,
            'date'            => '2020-10-30',
            'multicheckbox'   => '["1"]',
            'datatableselect' => '["1"]',
            'textarea'        => 6,
            'hidden'          => 7,
            'autocomplete'    => 8,
            'password'        => 9,
            'wysiwyg'         => 10,
        ]);

        $testRow = $I->getDbService()->getObject(
            (new Builder)->from(DataTableTest::class)->where('text = "testtext"')
        );

        $I->assertEquals($testRow->text, 'testtext');
        $I->assertEquals($testRow->file_id, 1);
        $I->assertEquals($testRow->checkbox, 1);
        $I->assertEquals($testRow->select, 1);
        $I->assertEquals($testRow->date, '2020-10-30 00:00:00');
        $I->assertEquals($testRow->multicheckbox, '["1"]');
        $I->assertEquals($testRow->datatableselect, '[1]');
        $I->assertEquals($testRow->textarea, 6);
        $I->assertEquals($testRow->hidden, 7);
        $I->assertEquals($testRow->autocomplete, 8);
        $I->assertEquals($testRow->password, 9);
        $I->assertEquals($testRow->wysiwyg, 10);
    }
}