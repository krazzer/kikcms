<?php
declare(strict_types=1);

namespace unit;

use Codeception\Test\Unit;
use DateTime;
use KikCMS\Objects\RememberMeHash;

class RememberMeHashTest extends Unit
{
    public function testSetExpire()
    {
        $rememberMeHash = new RememberMeHash(new DateTime, 'hash');

        $rememberMeHash->setExpire(new DateTime('2020-01-01'));

        $this->assertEquals('20200101', $rememberMeHash->getExpire()->format('Ymd'));

        $rememberMeHash->setHash('hashx');

        $this->assertEquals('hashx', $rememberMeHash->getHash());
    }
}
