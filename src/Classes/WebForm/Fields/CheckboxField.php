<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Check;

class CheckboxField extends Field
{
    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $validators = [])
    {
        $element = (new Check($key))
            ->setLabel($label)
            ->addValidators($validators);

        $this->element = $element;
        $this->key     = $key;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_CHECKBOX;
    }

    /**
     * @param $value
     * @return Field
     */
    public function setDefault($value): Field
    {
        if($value){
            $this->element->setAttribute('checked', '1');
        }

        return parent::setDefault($value);
    }
}