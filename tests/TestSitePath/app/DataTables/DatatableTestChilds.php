<?php declare(strict_types=1);

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\DatatableTestChildForm;
use Website\Models\DatatableTestChild;

class DatatableTestChilds extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return DatatableTestChildForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['datatabletestchild', 'datatabletestchilds'];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return DatatableTestChild::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            DatatableTestChild::FIELD_ID => 'Id',
            DatatableTestChild::FIELD_NAME => 'Name',
            DatatableTestChild::FIELD_PARENT_ID => 'Parent_id',
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
