<?php
declare(strict_types=1);

namespace Services\DataTable;

use KikCMS\Services\DataTable\PagesDataTableService;

class PagesDataTableServiceTest extends \Codeception\Test\Unit
{
    public function testFormatName()
    {
        $pagesDataTableService = new PagesDataTableService();

        // default
        $result   = $pagesDataTableService->formatName('value', [], false);
        $expected = '<span class="arrow"></span><span class="name">value</span>';

        $this->assertEquals($expected, $result);

        // with icons
        $result   = $pagesDataTableService->formatName('value', ['icon' => 'iconTitle'], false);
        $expected = '<span class="arrow"></span><span class="name"><span class="glyphicon glyphicon-icon" title="iconTitle"></span> value</span>';

        $this->assertEquals($expected, $result);

        // 2 icons
        $result   = $pagesDataTableService->formatName('value', ['i1' => 'it1', 'i2' => 'it2'], false);
        $expected = '<span class="arrow"></span><span class="name"><span class="glyphicon glyphicon-i2" title="it2"></span>' .
            ' <span class="glyphicon glyphicon-i1" title="it1"></span> value</span>';

        $this->assertEquals($expected, $result);

        // closed
        $result   = $pagesDataTableService->formatName('value', [], true);
        $expected = '<span class="arrow closed"></span><span class="name">value</span>';

        $this->assertEquals($expected, $result);
    }
}
