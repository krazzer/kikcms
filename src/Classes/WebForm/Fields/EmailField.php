<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Email;
use Phalcon\Filter\Validation\Validator\Email as EmailValidator;

class EmailField extends Field
{
    /**
     * @param string $key
     * @param string $label
     * @param bool $allowEmpty
     * @param array $validators
     */
    public function __construct(string $key, string $label, bool $allowEmpty = false, array $validators = [])
    {
        if( ! $this->hasEmailValidator($validators)){
            $validators[] = new EmailValidator(['allowEmpty' => $allowEmpty]);
        }

        $element = (new Email($key))
            ->setLabel($label)
            ->setAttribute('class', 'form-control')
            ->addValidators($validators);

        $this->element = $element;
        $this->key     = $key;
    }

    public function getInput($value)
    {
        return trim($value);
    }

    /**
     * @param array $validators
     * @return bool
     */
    private function hasEmailValidator(array $validators): bool
    {
        foreach ($validators as $validator){
            if($validator instanceof EmailValidator){
                return true;
            }
        }

        return false;
    }
}