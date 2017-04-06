<?php

namespace KikCMS\Classes\WebForm;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Classes\WebForm\DataForm\FieldStorage;
use Phalcon\Forms\Element;

/**
 * Represents a field of a form
 */
class Field
{
    const TYPE_AUTOCOMPLETE   = 'autocomplete';
    const TYPE_CHECKBOX       = 'checkbox';
    const TYPE_DATA_TABLE     = 'dataTable';
    const TYPE_MULTI_CHECKBOX = 'multiCheckbox';
    const TYPE_WYSIWYG        = 'wysiwyg';
    const TYPE_HIDDEN         = 'hidden';
    const TYPE_FILE           = 'file';
    const TYPE_BUTTON = 'button';
    const TYPE_DATE           = 'date';

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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this|WebForm
     */
    public function setForm(WebForm $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return Tab|null
     */
    public function getTab()
    {
        return $this->tab;
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
     * Shortcut to set the storage
     *
     * @param string $table
     * @param $relationKey
     * @param bool $addLanguageCode
     * @param array $defaultValues
     *
     * @return $this|DataForm
     */
    public function table(string $table, $relationKey, $addLanguageCode = false, $defaultValues = [])
    {
        $fieldStorage = $this->getNewFieldStorage();
        $fieldStorage->setField($this);
        $fieldStorage->setTableModel($table);
        $fieldStorage->setRelationKey($relationKey);
        $fieldStorage->setAddLanguageCode($addLanguageCode);
        $fieldStorage->setDefaultValues($defaultValues);

        $this->form->addFieldStorage($fieldStorage);

        return $this;
    }

    /**
     * @return FieldStorage
     */
    protected function getNewFieldStorage(): FieldStorage
    {
        return new FieldStorage();
    }
}