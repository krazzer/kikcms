<?php


namespace Website\Forms;


use KikCMS\Classes\WebForm\Fields\FileInputField;
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
        $dataTableField = $this->addDataTableField('dataTableTestChildren', DatatableTestChildren::class, 'SudDataTable');
        $this->addTextField('text', 'Text');
        $fileField = $this->addFileField('file_id', 'File');
        $this->addCheckboxField('checkbox', 'Chechbox');
        $this->addSelectField('select', 'Select', [1 => 1, 2 => 2])->addPlaceholder();
        $dateField = $this->addDateField('date', 'Date');
        $this->addMultiCheckboxField('multicheckbox', 'Multicheckbox', [1 => 1, 2 => 2])->setOptions([1 => 1, 2 => 2]);
        $this->addDataTableSelectField('datatableselect', new SimpleObjectsSelect, 'DataTableSelect');
        $this->addTextAreaField('textarea', 'Textarea');
        $this->addHiddenField('hidden', 1);
        $this->addAutoCompleteField('autocomplete', 'Autocomplete', '/autocomplete');
        $this->addPasswordField('password', 'Password');
        $this->addWysiwygField('wysiwyg', 'Rich text editor');

        $this->addHeader('testHeader')->setLabel('x');
        $this->addField(new FileInputField('file_input', 'FileInput'));
        $this->addHtmlField('html', 'label', 'content')->setContent('x')->setLabel('x');
        $this->addRadioButtonField('radio', 'radioLabel', [1 => 1])->setOptions([1 => 1]);
        $buttonField = $this->addButtonField('testButton', 'testButtonLabel', 'testButtonInfo', 'testButtonButtonLabel', '/testroute');

        $buttonField->setInfo('x');
        $buttonField->setButtonLabel('x');
        $buttonField->setRoute('x');
        $buttonField->setLabel('x');

        $dataTableField->getClass();
        $dateField->setStorageFormat('Y-m-d');

        $fileField->setFolderId(1);

        $this->addSection('testSection', [$buttonField]);
    }
}