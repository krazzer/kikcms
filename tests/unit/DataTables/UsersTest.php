<?php
declare(strict_types=1);

namespace unit\DataTables;

use Helpers\Unit;
use KikCMS\DataTables\Users;
use KikCMS\Forms\UserForm;

class UsersTest extends Unit
{
    public function testGetFormClass()
    {
        $users = new Users();

        $this->assertEquals(UserForm::class, $users->getFormClass());
    }
}
