<?php

namespace KikCMS\Classes\WebForm;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\FieldStorage;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\ManyToMany;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\None;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\OneToOne;
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

    /** @var string the table column where the value should be saved */
    private $column;

    /** @var bool whether this field is required or not, note that this does nothing with validation */
    private $required = false;

    /** @var FieldStorage|null contains how this field should be stored */
    private $storage;

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
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @param string $column
     * @return Field
     */
    public function setColumn(string $column): Field
    {
        $this->column = $column;
        return $this;
    }

    /**
     * @return FieldStorage|null
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Shortcut to set the storage to None
     */
    public function dontStore()
    {
        $this->store(new None());
    }

    /**
     * Shortcut to set the storage to OneToOne
     *
     * @param string $table
     * @param $relatedField
     * @param bool $addLanguageCode
     * @param array $defaultValues
     *
     * @return $this|Field
     */
    public function table(string $table, $relatedField, $addLanguageCode = false, $defaultValues = [])
    {
        $fieldStorage = (new OneToOne())
            ->setField($this)
            ->setTableModel($table)
            ->setRelatedField($relatedField)
            ->setAddLanguageCode($addLanguageCode)
            ->setDefaultValues($defaultValues);

        $this->store($fieldStorage);

        return $this;
    }

    /**
     * Shortcut to set the storage to ManyToMany
     *
     * @param string $table
     * @param string $relatedField
     * @param bool $addLanguageCode
     * @param array $defaultValues
     * @return $this|Field
     */
    public function tableMultiRow(string $table, string $relatedField, $addLanguageCode = false, $defaultValues = [])
    {
        $fieldStorage = (new ManyToMany())
            ->setField($this)
            ->setTableModel($table)
            ->setRelatedField($relatedField)
            ->setAddLanguageCode($addLanguageCode)
            ->setDefaultValues($defaultValues);

        $this->store($fieldStorage);

        return $this;
    }

    /**
     * Shortcut to set the storage in the cms_translation_value table
     *
     * @param null $langCode
     * @return $this
     */
    public function translate($langCode = null)
    {
        $fieldStorage = (new Translation())
            ->setField($this)
            ->setTableModel($this->form->getModel());

        if($langCode){
            $fieldStorage->setLanguageCode($langCode);
        }

        $this->store($fieldStorage);

        return $this;
    }

    /**
     * Shortcut for setting to storage
     *
     * @param FieldStorage $fieldStorage
     * @return Field|$this
     */
    public function store(FieldStorage $fieldStorage): Field
    {
        $this->storage = $fieldStorage;
        return $this;
    }
}