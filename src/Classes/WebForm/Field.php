<?php

namespace KikCMS\Classes\WebForm;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Classes\WebForm\DataForm\FieldStorage;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\MultiRow;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\Translation;
use Phalcon\Forms\Element;

/**
 * Represents a field of a form
 */
class Field
{
    const TYPE_AUTOCOMPLETE      = 'autocomplete';
    const TYPE_CHECKBOX          = 'checkbox';
    const TYPE_DATA_TABLE        = 'dataTable';
    const TYPE_SELECT_DATA_TABLE = 'selectDataTable';
    const TYPE_MULTI_CHECKBOX    = 'multiCheckbox';
    const TYPE_WYSIWYG           = 'wysiwyg';
    const TYPE_HIDDEN            = 'hidden';
    const TYPE_FILE              = 'file';
    const TYPE_BUTTON            = 'button';
    const TYPE_DATE              = 'date';

    /** @var WebForm|DataForm */
    protected $form;

    /** @var Element|null */
    private $element;

    /** @var string */
    private $key;

    /** @var Tab */
    private $tab;

    /** @var string the table field where the value should be saved */
    private $tableField;

    /** @var bool whether this field is required or not, note that this does nothing with validation */
    private $required = false;

    /**
     * @param Element $element
     */
    public function __construct(Element $element = null)
    {
        $this->element = $element;
    }

    /**
     * Add a class to the element
     *
     * @param string $class
     * @return $this|Field
     */
    public function addElementClass(string $class)
    {
        $classes = explode(' ', $this->getElement()->getAttribute('class'));

        if ( ! in_array($class, $classes)) {
            $classes[] = $class;
        }

        $this->getElement()->setAttribute('class', implode(' ', $classes));

        return $this;
    }

    /**
     * @param $value
     * @return $this|Field
     */
    public function setDefault($value): Field
    {
        $this->element->setDefault($value);

        return $this;
    }

    /**
     * @param string $value
     * @return $this|Field
     */
    public function setPlaceholder(string $value): Field
    {
        $this->setAttribute('placeholder', $value);

        return $this;
    }

    /**
     * @return Element|null
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     *
     * @return $this|Field
     */
    public function setAttribute(string $attribute, $value)
    {
        $this->element->setAttribute($attribute, $value);

        return $this;
    }

    /**
     * @param Element $element
     * @return $this|Field
     */
    public function setElement(Element $element): Field
    {
        $this->element = $element;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this|Field
     */
    public function setKey(string $key)
    {
        $this->key = $key;

        if ($this->element) {
            $this->element->setName($key);
        }

        return $this;
    }

    /**
     * @param Webform $form
     * @return $this|Field
     */
    public function setForm(WebForm $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Override this method to convert the field's value to a format best handled by PHP
     * e.g. convert a json encoded object to an actual PHP object
     *
     * @param $value
     * @return mixed
     */
    public function getInput($value)
    {
        return $value;
    }

    /**
     * Override this method to convert the field's value to a format that is required in the form
     * e.g. json encode an array or object
     *
     * @param $value
     * @return mixed
     */
    public function getFormFormat($value)
    {
        return $value;
    }

    /**
     * @return Tab|null
     */
    public function getTab()
    {
        return $this->tab;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return $this|Field
     */
    public function setRequired(bool $required = true): Field
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @param Tab $tab
     * @return Field
     */
    public function setTab(Tab $tab): Field
    {
        $this->tab = $tab;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableField(): string
    {
        return $this->tableField;
    }

    /**
     * @param string $tableField
     * @return Field
     */
    public function setTableField(string $tableField): Field
    {
        $this->tableField = $tableField;
        return $this;
    }

    /**
     * Shortcut to set the storage to different table
     *
     * @param string $table
     * @param $relationKey
     * @param bool $addLanguageCode
     * @param array $defaultValues
     *
     * @return $this|Field
     */
    public function table(string $table, $relationKey, $addLanguageCode = false, $defaultValues = [])
    {
        $fieldStorage = (new FieldStorage())
            ->setField($this)
            ->setTableModel($table)
            ->setRelationKey($relationKey)
            ->setAddLanguageCode($addLanguageCode)
            ->setDefaultValues($defaultValues);

        $this->form->addFieldStorage($fieldStorage);

        return $this;
    }

    /**
     * Shortcut to set the storage to MultiRow
     *
     * @param string $table
     * @param $relationKey
     * @param bool $addLanguageCode
     * @param array $defaultValues
     *
     * @return $this|Field
     */
    public function tableMultiRow(string $table, $relationKey, $addLanguageCode = false, $defaultValues = [])
    {
        $fieldStorage = (new MultiRow())
            ->setField($this)
            ->setTableModel($table)
            ->setRelationKey($relationKey)
            ->setAddLanguageCode($addLanguageCode)
            ->setDefaultValues($defaultValues);

        $this->form->addFieldStorage($fieldStorage);

        return $this;
    }

    /**
     * Shortcut to set the storage in the cms_translation_value table
     * @param null $languageCode
     * @return $this
     */
    public function translate($languageCode = null)
    {
        $fieldStorage = new Translation();
        $fieldStorage->setField($this);
        $fieldStorage->setTableModel($this->form->getModel());

        if($languageCode){
            $fieldStorage->setLanguageCode($languageCode);
        }

        $this->form->addFieldStorage($fieldStorage);

        return $this;
    }
}