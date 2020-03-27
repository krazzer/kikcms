<?php
declare(strict_types=1);

namespace DataTables\Filter;

use Helpers\Unit;
use KikCMS\DataTables\Filter\FilterSelectPageParent;
use KikCMS\Models\Page;
use Phalcon\Mvc\Model\Query\Builder;

class FilterSelectPageParentTest extends Unit
{
    public function testApplyFilter()
    {
        $this->getDbDi();

        $filterSelectPageParent = new FilterSelectPageParent('field', 'label', [1 => 'opt1', 2 => 'opt2']);

        $query = (new Builder);

        $page      = new Page();
        $page->id  = 1;
        $page->lft = 1;
        $page->rgt = 4;

        $page->save();

        $this->assertEmpty($query->getWhere());

        $filterSelectPageParent->applyFilter($query, 1);

        $this->assertNotEmpty($query->getWhere());
    }
}
