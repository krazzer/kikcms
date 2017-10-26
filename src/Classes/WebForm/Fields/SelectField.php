<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element;
use Phalcon\Forms\Element\Select;

class SelectField extends Field
{
    /** @var bool */
    private $addPlaceholder = false;

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $options, array $validators = [])
    {
        $element = (new Select($key))
            ->setOptions($options)
            ->setLabel($label)
            ->setAttribute('class', 'form-control')
            ->addValidators($validators);

        $this->element = $element;
    }

    /**
     * @return SelectField
     */
    public function addPlaceholder(): SelectField
    {
        $this->addPlaceholder = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAddPlaceholder(): bool
    {
        return $this->addPlaceholder;
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
        return Field::TYPE_SELECT;
    }
}