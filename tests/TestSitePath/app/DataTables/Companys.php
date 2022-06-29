<?php declare(strict_types=1);

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\CompanyForm;
use Website\Models\TestCompany;

class Companys extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return CompanyForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['company', 'companys'];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return TestCompany::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            TestCompany::FIELD_ID   => 'Id',
            TestCompany::FIELD_NAME => 'Name',
        ];
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        // nothing here...
    }
}
