<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element;
use Phalcon\Forms\Element\Radio;
use Phalcon\Forms\Element\Select;

class RadioButtonField extends Field
{
    /** @var array */
    private $options;

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $options, array $validators = [])
    {
        $element = (new Radio($key))
            ->setLabel($label)
            ->setAttribute('class', 'form-control')
            ->addValidators($validators);

        $this->element = $element;
        $this->options = $options;
    }

    /**
     * @return null|Element|Select
     */
    public function getElement(): ?Element
    {
        return parent::getElement();
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_RADIOBUTTON;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return RadioButtonField
     */
    public function setOptions(array $options): RadioButtonField
    {
        $this->options = $options;
        return $this;
    }
}