<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Email;
use Phalcon\Filter\Validation\Validator\Numericality;

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

    /**
     * @param $value
     * @return mixed
     */
    public function getInput($value): mixed
    {
        if($this->hasEmailValidator()){
            $value = trim($value);
        }

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

    /**
     * @return bool
     */
    private function hasEmailValidator(): bool
    {
        foreach ($this->getElement()->getValidators() as $validator){
            if($validator instanceof Email){
                return true;
            }
        }

        return false;
    }
}