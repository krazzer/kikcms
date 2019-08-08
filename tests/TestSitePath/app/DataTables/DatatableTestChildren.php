<?php

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\DatatableTestChildForm;
use Website\Models\DataTableTestChild;

class DatatableTestChildren extends DataTable
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
        return DataTableTestChild::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            DataTableTestChild::FIELD_ID        => 'Id',
            DataTableTestChild::FIELD_NAME      => 'Name',
            DataTableTestChild::FIELD_PARENT_ID => 'Parent_id',
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
