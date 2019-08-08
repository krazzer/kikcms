<?php

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\SimpleObjectForm;
use Website\Models\SimpleObject;

class SimpleObjects extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return SimpleObjectForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['simpleobject', 'simpleobjects'];
    }

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
    public function getTableFieldMap(): array
    {
        return [
            SimpleObject::FIELD_ID => 'Id',
            SimpleObject::FIELD_NAME => 'Name',
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
