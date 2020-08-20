<?php
declare(strict_types=1);

namespace unit\Forms;

use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Forms\UserForm;
use KikCMS\Models\User;
use KikCMS\Services\Cms\CmsService;
use Phalcon\Validation;
use ReflectionMethod;

class UserFormTest extends Unit
{
    public function testGetModel()
    {
        $userForm = new UserForm();

        $this->assertEquals(User::class, $userForm->getModel());
    }

    public function testInitialize()
    {
        $userForm = new UserForm();

        $cmsService = $this->createMock(CmsService::class);
        $cmsService->method('getRoleMap')->willReturn([]);

        $userForm->translator = (new TestHelper)->getTranslator();
        $userForm->validation = new Validation();
        $userForm->cmsService = $cmsService;

        $method = new ReflectionMethod(UserForm::class, 'initialize');
        $method->setAccessible(true);

        $method->invoke($userForm);
    }
}
