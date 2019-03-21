<?php


namespace DataTables;


use KikCMS\Classes\DataTable\DataTable;
use Models\PersonInterest;

class PersonInterests extends DataTable
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