<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class TextField extends Field
{
    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $validators = [])
    {
        $element = (new Text($key))
            ->setLabel($label)
            ->setAttribute('class', 'form-control')
            ->addValidators($validators);

        $this->element = $element;
        $this->key     = $key;
    }

    public function getInput($value)
    {
        if($this->isNumeric()){
            return str_replace(',', '.', $value);
        }

        return $value;
    }

    /**
     * @return bool
     */
    private function isNumeric(): bool
    {
        foreach ($this->getElement()->getValidators() as $validator){
            if($validator instanceof Numericality){
                return true;
            }
        }

        return false;
    }
}