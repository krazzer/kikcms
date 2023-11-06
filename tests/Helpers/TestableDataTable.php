<?php


namespace Helpers;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DataTable\DataTableFilters;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Forms\UserForm;
use KikCMS\Models\User;

class TestableDataTable extends DataTable
{
    protected $searchableFields = ['test'];
    protected $sortableField    = 'test';
    protected $multiLingual     = true;
    protected $sortable         = true;
    protected $sortableNewFirst = true;

    public function __construct(?Filters $filters = null)
    {
        $filters = (new DataTableFilters)->setLanguageCode('nl');

        parent::__construct($filters);
    }

    public function getModel(): string
    {
        return User::class;
    }

    public function getFormClass(): string
    {
        return UserForm::class;
    }

    public function testableGetDefaultQuery()
    {
        return $this->getDefaultQuery();
    }

    public function formatBoolean($value): string
    {
        return parent::formatBoolean($value);
    }

    public function formatCheckbox($value, $rowData, $column): string
    {
        return parent::formatCheckbox($value, $rowData, $column);
    }

    public function formatFinderImage($value): string
    {
        return parent::formatFinderImage($value);
    }

    protected function initialize()
    {
        // nothing as of yet
    }
}