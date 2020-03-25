<?php
declare(strict_types=1);

use Helpers\Unit;
use KikCMS\Models\User;
use KikCmsCore\Services\DbService;
use Phalcon\Mvc\Model\Query\Builder;

class SqliteTest extends Unit
{
    public function testConcatWs()
    {
        $di = $this->getDbDi();

        /** @var DbService $dbService */
        $dbService = $di->get('dbService');

        $this->addUser(1, 'a', 'b');

        $query = (new Builder)->from(User::class)->columns('CONCAT_WS(" ", id, email, role)');

        $this->assertEquals('1 a b', $dbService->getValue($query));

        $query = (new Builder)->from(User::class)->columns('CONCAT_WS(" ", id)');

        $this->assertEquals('1', $dbService->getValue($query));

        $query = (new Builder)->from(User::class)->columns('CONCAT_WS(id)');

        $this->expectException(Exception::class);

        $dbService->getValue($query);
    }

    /**
     * @param int $id
     * @param string $email
     * @param string $role
     * @throws Exception
     */
    private function addUser(int $id, string $email, string $role)
    {
        $user = new User();

        $user->id      = $id;
        $user->email   = $email;
        $user->role    = $role;
        $user->blocked = 0;

        $user->save();
    }
}
