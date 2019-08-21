<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Text;

class AutocompleteField extends Field
{
    /**
     * @param string $key
     * @param string $label
     * @param string $route
     * @param array $validators
     */
    public function __construct(string $key, string $label, string $route, array $validators = [])
    {
        $element = (new Text($key))
            ->setLabel($label)
            ->setAttribute('class', 'form-control autocomplete')
            ->setAttribute('autocomplete', 'off')
            ->setAttribute('data-field-key', $key)
            ->setAttribute('data-route', $route)
            ->addValidators($validators);

        $this->element = $element;
        $this->key     = $key;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_AUTOCOMPLETE;
    }
}