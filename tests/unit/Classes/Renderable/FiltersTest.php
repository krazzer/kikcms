<?php

namespace KikCMS\Classes\Renderable\Filters;

use Codeception\Test\Unit;
use KikCMS\Classes\WebForm\DataForm\DataFormFilters;

class FiltersTest extends Unit
{
    public function testSetByArray()
    {
        $filters = new DataFormFilters();

        $filters->setByArray(['editId' => 1]);

        $this->assertEquals(1, $filters->getEditId());

        // test non existing property
        $filters->setByArray(['nonExistingProperty' => 1]);

        $this->assertEquals(1, $filters->getEditId());

        // test null
        $filters->setByArray(['languageCode' => '']);

        $this->assertEquals(null, $filters->getLanguageCode());
    }
}
