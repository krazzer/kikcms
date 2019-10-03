<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;
use Phalcon\Mvc\Model\Query\Builder;
use Website\Models\TestPerson;

class DataFormCest
{
    public function renderWorks(FunctionalTester $I)
    {
        $I->login();

        //TestUserPass
        $I->amOnPage('/cms/test/personform');
        $I->seeElement('#webFormId_WebsiteFormsTestPersonForm');

        $I->submitForm('#webFormId_WebsiteFormsTestPersonForm form', [
            'name'    => 'test',
            'created' => '30-10-2020',
        ]);

        $persons = $I->getDbService()->getObjects((new Builder)->from(TestPerson::class));

        $I->assertCount(1, $persons);

        $I->cleanDb();
    }
}