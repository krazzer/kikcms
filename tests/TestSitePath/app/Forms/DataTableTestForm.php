<?php


namespace Website\Forms;


use Website\DataTables\DatatableTestChildren;
use Website\DataTables\SimpleObjectsSelect;
use Website\Models\DataTableTest;
use KikCMS\Classes\WebForm\DataForm\DataForm;

class DataTableTestForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return DataTableTest::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addDataTableField('dataTableTestChildren', DatatableTestChildren::class, 'SudDataTable');
        $this->addTextField('text', 'Text');
        $this->addFileField('file_id', 'File');
        $this->addCheckboxField('checkbox', 'Chechbox');
        $this->addSelectField('select', 'Select', [1 => 1, 2 => 2]);
        $this->addDateField('date', 'Date');
        $this->addMultiCheckboxField('multicheckbox', 'Multicheckbox', [1 => 1, 2 => 2]);
        $this->addDataTableSelectField('datatableselect', new SimpleObjectsSelect, 'DataTableSelect');
        $this->addTextAreaField('textarea', 'Textarea');
        $this->addHiddenField('hidden', 1);
        $this->addAutoCompleteField('autocomplete', 'Autocomplete', '/autocomplete');
        $this->addPasswordField('password', 'Password');
        $this->addWysiwygField('wysiwyg', 'Rich text editor');
    }
}