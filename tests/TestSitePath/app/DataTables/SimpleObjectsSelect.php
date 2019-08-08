<?php


namespace Website\DataTables;


use KikCMS\Classes\DataTable\SelectDataTable;
use Website\Models\SimpleObject;

class SimpleObjectsSelect extends SelectDataTable
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return SimpleObject::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // nothing here...
    }
}