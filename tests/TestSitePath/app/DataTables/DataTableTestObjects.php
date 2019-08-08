<?php


namespace Website\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\DataTableTestForm;
use Website\Models\DataTableTest;

class DataTableTestObjects extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return DataTableTest::class;
    }

    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return DataTableTestForm::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // nothing here...
    }
}