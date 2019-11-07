<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;

class ErrorsCest
{
    public function _before(FunctionalTester $I)
    {
        $I->login();
    }

    public function show404Works(FunctionalTester $I)
    {
        $I->amOnPage('/cms/error/show404');
        $I->canSeeResponseCodeIs(404);
    }

    public function show401Works(FunctionalTester $I)
    {
        $I->amOnPage('/cms/error/show401');
        $I->canSeeResponseCodeIs(401);
    }

    public function show500Works(FunctionalTester $I)
    {
        $I->amOnPage('/cms/error/show500');
        $I->canSeeResponseCodeIs(500);
    }

    public function show404ObjectWorks(FunctionalTester $I)
    {
        $I->amOnPage('/cms/error/show404object/object');
        $I->canSeeResponseCodeIs(404);
    }
}