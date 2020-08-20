<?php
declare(strict_types=1);

namespace unit\Classes\Phalcon\Validator;

use Helpers\Unit;
use KikCMS\Classes\Phalcon\Validator\NewUniqueness;
use KikCMS\Models\User;
use Phalcon\Validation;

class NewUniquenessTest extends Unit
{
    public function testValidate()
    {
        $dbDi = $this->getDbDi();

        $validation = new Validation();
        $validation->add('email', new NewUniqueness(['id' => 1, 'model' => new User]));

        // is unique
        $messages = $validation->validate(['email' => 'some@email.com']);
        $this->assertCount(0, $messages);

        // is not unique, but not new, so should validate
        $dbDi->get('dbService')->insert(User::class, ['id' => 1, 'email' => 'some@email.com', 'blocked' => 0, 'role' => 'admin']);
        $messages = $validation->validate(['email' => 'some@email.com']);
        $this->assertCount(0, $messages);

        // is not unique
        $dbDi->get('dbService')->delete(User::class, ['id' => 1]);
        $dbDi->get('dbService')->insert(User::class, ['id' => 2, 'email' => 'some@email.com', 'blocked' => 0, 'role' => 'admin']);
        $messages = $validation->validate(['email' => 'some@email.com']);
        $this->assertCount(1, $messages);

        // is unique
        $messages = $validation->validate(['email' => 'someother@email.com']);
        $this->assertCount(0, $messages);

        // is not unique by another id
        $dbDi->get('dbService')->insert(User::class, ['id' => 1, 'email' => 'someother@email.com', 'blocked' => 0, 'role' => 'admin']);
        $messages = $validation->validate(['email' => 'some@email.com']);
        $this->assertCount(1, $messages);
    }
}
