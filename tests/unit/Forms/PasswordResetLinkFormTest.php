<?php
declare(strict_types=1);

namespace unit\Forms;

use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Forms\PasswordResetLinkForm;
use KikCMS\Models\User;
use KikCMS\Services\UserService;
use Phalcon\Flash;
use ReflectionMethod;

class PasswordResetLinkFormTest extends Unit
{
    public function testSuccessAction()
    {
        $passwordResetLinkForm = new PasswordResetLinkForm();

        $userService = $this->createMock(UserService::class);
        $userService->method('getByEmail')->willReturn(null);

        $flash = $this->createMock(Flash::class);

        $passwordResetLinkForm->userService = $userService;
        $passwordResetLinkForm->flash       = $flash;
        $passwordResetLinkForm->translator  = (new TestHelper)->getTranslator();

        $method = new ReflectionMethod(PasswordResetLinkForm::class, 'successAction');
        $method->setAccessible(true);

        $method->invoke($passwordResetLinkForm, ['email' => 'x']);

        $user = $this->createMock(User::class);

        $userService = $this->createMock(UserService::class);
        $userService->method('getByEmail')->willReturn($user);
        $userService->method('sendResetMail')->willReturn(false);

        $flash = $this->createMock(Flash::class);
        $flash->expects($this->once())->method('error');

        $passwordResetLinkForm->flash       = $flash;
        $passwordResetLinkForm->userService = $userService;

        $method->invoke($passwordResetLinkForm, ['email' => 'x']);
    }
}
