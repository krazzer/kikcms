<?php
declare(strict_types=1);

namespace Helpers\DataTables;


use KikCMS\Classes\DataTable\SelectDataTable;

class TestSelectDataTable extends SelectDataTable
{
    public function getModel(): string
    {
        return '';
    }

    public function getAlias(): ?string
    {
        return 'a';
    }

    protected function initialize()
    {
        // nothing here...
    }
}