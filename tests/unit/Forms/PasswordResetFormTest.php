<?php
declare(strict_types=1);

namespace unit\Forms;

use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Forms\PasswordResetForm;
use KikCMS\Models\User;
use KikCMS\Services\UserService;
use ReflectionMethod;

class PasswordResetFormTest extends Unit
{
    public function testGetSuccessMessage()
    {
        $passwordResetForm = new PasswordResetForm();

        $method = new ReflectionMethod(PasswordResetForm::class, 'getSuccessMessage');
        $method->setAccessible(true);

        $userService = $this->createMock(UserService::class);
        $userService->method('isLoggedIn')->willReturn(true);
        $userService->method('getUserId')->willReturn(1);

        $passwordResetForm->translator  = (new TestHelper)->getTranslator();
        $passwordResetForm->userService = $userService;

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $passwordResetForm->setUser($user);

        $this->assertEquals('Your password has been updated.', $method->invoke($passwordResetForm));

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(2);
        $user->method('__get')->with('email')->willReturn('x');

        $passwordResetForm->setUser($user);

        $this->assertEquals('The password for <b>x</b> has been updated.', $method->invoke($passwordResetForm));
    }
}
