<?php

namespace unit\Classes\Database;

use KikCMS\Classes\Database\Now;
use PHPUnit\Framework\TestCase;

class NowTest extends TestCase
{
    public function testConstruct()
    {
        $now = new Now();

        $this->assertEquals(get_class($now), Now::class);
    }

    public function testToString()
    {
        $now = new Now();

        $this->assertEquals(strlen($now->__toString()), 19);
    }
}
