<?php

namespace KikCMS\Classes\WebForm;


use Phalcon\Forms\Element;

/**
 * Represents a field of a form
 */
class Field
{
    const TYPE_CHECKBOX       = 'checkbox';
    const TYPE_MULTI_CHECKBOX = 'multiCheckbox';

    /** @var Element */
    private $element;

    /** @var string */
    private $type;

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
    public function getType(): string
    {
        return $this->type ?: '';
    }

    /**
     * @param string $type
     * @return $this|Field
     */
    public function setType(string $type): Field
    {
        $this->type = $type;
        return $this;
    }
}