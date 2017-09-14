<?php

namespace KikCMS\Classes\WebForm;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DataTable\SelectDataTable;
use KikCMS\Classes\WebForm\Fields\AutocompleteField;
use KikCMS\Classes\WebForm\Fields\ButtonField;
use KikCMS\Classes\WebForm\Fields\CheckboxField;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Classes\WebForm\Fields\DateField;
use KikCMS\Classes\WebForm\Fields\FileField;
use KikCMS\Classes\WebForm\Fields\HiddenField;
use KikCMS\Classes\WebForm\Fields\HtmlField;
use KikCMS\Classes\WebForm\Fields\MultiCheckboxField;
use KikCMS\Classes\WebForm\Fields\PasswordField;
use KikCMS\Classes\WebForm\Fields\SelectDataTableField;
use KikCMS\Classes\WebForm\Fields\SelectField;
use KikCMS\Classes\WebForm\Fields\TextareaField;
use KikCMS\Classes\WebForm\Fields\TextField;
use KikCMS\Classes\WebForm\Fields\WysiwygField;

trait FieldShortcuts
{
    /**
     * @param Field $field
     * @return Field
     */
    public abstract function addField(Field $field);

    /**
     * @param string $key
     * @param string $label
     * @param string $route
     * @param array $validators
     * @return AutocompleteField|Field
     */
    public function addAutoCompleteField(string $key, string $label, string $route, array $validators = []): Field
    {
        return $this->addField(new AutocompleteField($key, $label, $route, $validators));
    }

    /**
     * @param string $label
     * @param string $info
     * @param string $buttonLabel
     * @param string $route
     * @return ButtonField|Field
     */
    public function addButtonField(string $label, string $info, string $buttonLabel, string $route): Field
    {
        return $this->addField(new ButtonField($label, $info, $buttonLabel, $route));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return CheckboxField|Field
     */
    public function addCheckboxField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new CheckboxField($key, $label, $validators));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return DateField|Field
     */
    public function addDateField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new DateField($key, $label, $validators));
    }

    /**
     * @param DataTable $dataTable
     * @param string $label
     *
     * @return Field|DataTableField
     */
    public function addDataTableField(DataTable $dataTable, string $label)
    {
        return $this->addField(new DataTableField($dataTable, $label));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|FileField
     */
    public function addFileField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new FileField($key, $label, $validators));
    }

    /**
     * Add HTML to a form
     *
     * @param string $label
     * @param string $content
     * @return Field|HtmlField
     */
    public function addHtml(string $label, string $content)
    {
        return $this->addField(new HtmlField($label, $content));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|PasswordField
     */
    public function addPasswordField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new PasswordField($key, $label, $validators));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     * @return Field|MultiCheckboxField
     */
    public function addMultiCheckboxField(string $key, string $label, array $options): Field
    {
        return $this->addField(new MultiCheckboxField($key, $label, $options));
    }

    /**
     * @param string $key
     * @param SelectDataTable $dataTable
     * @param string $label
     * @return Field|SelectDataTableField
     */
    public function addDataTableSelectField(string $key, SelectDataTable $dataTable, string $label)
    {
        return $this->addField(new SelectDataTableField($key, $dataTable, $label));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     * @param array $validators
     * @return Field
     */
    public function addSelectField(string $key, string $label, array $options, array $validators = []): Field
    {
        return $this->addField(new SelectField($key, $label, $options, $validators));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field
     */
    public function addTextField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new TextField($key, $label, $validators));
    }
    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|TextareaField
     */
    public function addTextAreaField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new TextareaField($key, $label, $validators));
    }
    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|WysiwygField
     */
    public function addWysiwygField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new WysiwygField($key, $label, $validators));
    }
    /**
     * @param string $key
     * @param mixed $defaultValue
     * @return Field
     */
    public function addHiddenField(string $key, $defaultValue = null): Field
    {
        return $this->addField(new HiddenField($key, $defaultValue));
    }
}