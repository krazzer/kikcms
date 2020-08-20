<?php
declare(strict_types=1);

namespace unit;

use Codeception\Test\Unit;
use KikCMS\Objects\UserSettings;

class UserSettingsTest extends Unit
{
    public function testSerialize()
    {
        $userSettings = new UserSettings();

        $this->assertNull($userSettings->serialize());

        $userSettings->setClosedPageIdMap([1 => 1]);

        $serialized = 'a:1:{i:1;i:1;}';

        $this->assertEquals($serialized, $userSettings->serialize());

        $newUserSettings = new UserSettings();
        $newUserSettings->setClosedPageIdMap([1 => 2]);

        $this->assertEquals([1 => 1], $newUserSettings->unserialize($serialized)->getClosedPageIdMap());
    }
}
