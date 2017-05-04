<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DataTable\SelectDataTable;
use KikCMS\Classes\WebForm\Field;

class SelectDataTableField extends DataTableField
{
    /**
     * @inheritdoc
     */
    public function getInput($value)
    {
        return json_decode($value);
    }

    /**
     * @inheritdoc
     */
    public function getFormFormat($value)
    {
        return json_encode($value);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_SELECT_DATA_TABLE;
    }

    /**
     * @return DataTable|SelectDataTable
     */
    public function getDataTable(): DataTable
    {
        return parent::getDataTable();
    }
}