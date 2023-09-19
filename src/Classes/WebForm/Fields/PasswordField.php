<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Password;

class PasswordField extends Field
{
    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $validators)
    {
        $element = (new Password($key))
            ->setLabel($label)
            ->setAttribute('class', 'form-control')
            ->addValidators($validators);

        $this->element = $element;
        $this->key     = $key;
    }

    /**
     * @inheritdoc
     */
    public function getType(): ?string
    {
        return Field::TYPE_PASSWORD;
    }
}