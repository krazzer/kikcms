<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;

class CacheCest
{
    public function _before(FunctionalTester $I)
    {
        $I->login();
    }

    public function managerWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/cache/');
        $I->seeElement('.tree');
    }

    public function emptyByKeyWorks(FunctionalTester $I)
    {
        $I->getCache()->set('test', 'test', 1000);

        $I->assertTrue($I->getCache()->has('test'));

        $I->sendAjaxPostRequest('/cms/cache/empty', ['key' => 'test']);
        $I->seeInCurrentUrl('/cms/cache');

        $I->assertFalse($I->getCache()->has('test'));
    }
}