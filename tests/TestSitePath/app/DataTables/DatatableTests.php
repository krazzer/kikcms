<?php declare(strict_types=1);

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\DatatableTestForm;
use Website\Models\DatatableTest;

class DatatableTests extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return DatatableTestForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['datatabletest', 'datatabletests'];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return DatatableTest::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            DatatableTest::FIELD_ID => 'Id',
            DatatableTest::FIELD_TEXT => 'Text',
            DatatableTest::FIELD_FILE_ID => 'File_id',
            DatatableTest::FIELD_CHECKBOX => 'Checkbox',
            DatatableTest::FIELD_DATE => 'Date',
            DatatableTest::FIELD_MULTICHECKBOX => 'Multicheckbox',
            DatatableTest::FIELD_DATATABLESELECT => 'Datatableselect',
            DatatableTest::FIELD_TEXTAREA => 'Textarea',
            DatatableTest::FIELD_HIDDEN => 'Hidden',
            DatatableTest::FIELD_AUTOCOMPLETE => 'Autocomplete',
            DatatableTest::FIELD_PASSWORD => 'Password',
            DatatableTest::FIELD_WYSIWYG => 'Wysiwyg',
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
