<?php declare(strict_types=1);


namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Hidden;

class ReCaptchaField extends Field
{
    /**
     * @param string $label
     * @param array $validators
     */
    public function __construct(string $label = null, array $validators)
    {
        $this->key   = 'captcha';
        $this->label = $label;

        $this->element = (new Hidden($this->key))->addValidators($validators);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_RECAPTCHA;
    }
}