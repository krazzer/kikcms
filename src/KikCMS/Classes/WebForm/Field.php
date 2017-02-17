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
    const TYPE_DATE           = 'date';

    /** @var WebForm|DataForm */
    protected $form;

    /** @var Element */
    private $element;

    /** @var Tab */
    private $tab;

    /**
     * @param Element $element
     */
    public function __construct(Element $element)
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
     * @return Element
     */
    public function getElement(): Element
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
        return $this->element->getName();
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
     * Shortcut to set the storage
     *
     * @param string $table
     * @param $relationKey
     *
     * @return $this|DataForm
     */
    public function table(string $table, $relationKey)
    {
        $fieldStorage = new FieldStorage();
        $fieldStorage->setField($this);
        $fieldStorage->setTableModel($table);
        $fieldStorage->setRelationKey($relationKey);

        $this->form->addFieldStorage($fieldStorage);

        return $this;
    }
}