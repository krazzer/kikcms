<?php
declare(strict_types=1);

namespace unit;

use Codeception\Test\Unit;
use KikCMS\Objects\CmsMenuItem;

class CmsMenuItemTest extends Unit
{
    public function testGetAndSet()
    {
        $menuItem = new CmsMenuItem('id', 'label', 'route');

        $menuItem->setId('idx');

        $this->assertEquals('idx', $menuItem->getId());

        $menuItem->setRoute('routex');

        $this->assertEquals('routex', $menuItem->getRoute());

        $menuItem->setLabel('labelx');

        $this->assertEquals('labelx', $menuItem->getLabel());

        $menuItem->setTargetBlank(true);

        $this->assertTrue($menuItem->isTargetBlank());
    }
}
