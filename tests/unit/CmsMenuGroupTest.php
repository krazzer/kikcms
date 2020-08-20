<?php
declare(strict_types=1);

namespace unit;

use Codeception\Test\Unit;
use KikCMS\Objects\CmsMenuGroup;

class CmsMenuGroupTest extends Unit
{
    public function testGetAndSet()
    {
        $cmsMenuGroup = new CmsMenuGroup('id', 'label');

        $this->assertEquals('id', $cmsMenuGroup->getId());

        $cmsMenuGroup->setId('idx');

        $this->assertEquals('idx', $cmsMenuGroup->getId());

        $cmsMenuGroup->setLabel('labelx');

        $this->assertEquals('labelx', $cmsMenuGroup->getLabel());
    }
}
