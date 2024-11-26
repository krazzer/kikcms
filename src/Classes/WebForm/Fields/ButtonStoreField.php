<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\Phalcon\Forms\Element\Button;
use KikCMS\Classes\WebForm\Field;

class ButtonStoreField extends Field
{
    /**
     * @param string $key
     * @param string $label
     */
    public function __construct(string $key, string $label)
    {
        $element = (new Button($key))
            ->setLabel($label);

        $this->element = $element;
        $this->key     = $key;
    }
}