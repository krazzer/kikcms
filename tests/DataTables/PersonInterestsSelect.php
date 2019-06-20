<?php


namespace DataTables;


use KikCMS\Classes\DataTable\SelectDataTable;
use Models\PersonInterest;

class PersonInterestsSelect extends SelectDataTable
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return PersonInterest::class;
    }

    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // nothing
    }
}