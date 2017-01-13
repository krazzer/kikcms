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
    const TYPE_MULTI_CHECKBOX = 'multiCheckbox';
    const TYPE_WYSIWYG        = 'wysiwys';

    /** @var WebForm|DataForm */
    protected $form;

    /** @var Element */
    private $element;

    /**
     * @param Element $element
     */
    public function __construct(Element $element)
    {
        $this->element = $element;
    }

    /**
     * @return Element
     */
    public function getElement(): Element
    {
        return $this->element;
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