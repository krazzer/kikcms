<?php

use KikCMS\Models\User;


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    const TEST_USERNAME = 'test@test.com';

    /**
     * @param string $username
     * @param string $password
     */
    public function login(string $username = self::TEST_USERNAME, $password = 'TestUserPass')
    {
        $I = $this;

        $I->addUser();

        $I->amOnPage('/cms');
        $I->submitForm('#login-form form', [
            'username' => $username,
            'password' => $password,
            'remember' => null,
        ]);
    }

    /**
     * Add a user to the DB
     */
    private function addUser()
    {
        $this->haveInDatabase(User::TABLE, [
            User::FIELD_PASSWORD => '$2y$10$I1eyBL8OVtc8QP6YaiMC5uAkUyH7LMJmUlrzUTOC5vvX/kXJrk1.y',
            User::FIELD_EMAIL    => self::TEST_USERNAME,
            User::FIELD_ROLE     => 'developer',
            User::FIELD_ID       => 1,
        ]);
    }
}
