<?php


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
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    public function login(string $username = 'test@test', $password = 'TestUserPass')
    {
        $I = $this;
        $I->amOnPage('/cms');
        $I->submitForm('#login-form form', [
            'username' => $username,
            'password' => $password,
            'remember' => null,
        ]);

        $I->seeElement('#menu');
    }
}
