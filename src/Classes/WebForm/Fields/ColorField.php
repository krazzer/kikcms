<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\Phalcon\Forms\Element\ColorPicker;
use KikCMS\Classes\WebForm\Field;

class ColorField extends Field
{
    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $validators = [])
    {
        $element = (new ColorPicker($key))
            ->setLabel($label)
            ->addValidators($validators);

        $this->element = $element;
        $this->key     = $key;
    }
}