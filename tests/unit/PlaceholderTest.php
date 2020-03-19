<?php
declare(strict_types=1);

use Codeception\Test\Unit;
use KikCMS\Objects\PlaceholderFileUrl;

class PlaceholderTest extends Unit
{
    public function testGetPlaceholder()
    {
        $placeholder = new PlaceholderFileUrl('key', 'placeholder', [1, null]);

        $this->assertEquals('placeholder', $placeholder->getPlaceholder());
    }
}
