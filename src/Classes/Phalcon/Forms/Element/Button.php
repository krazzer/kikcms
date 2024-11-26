<?php

namespace KikCMS\Classes\Phalcon\Forms\Element;

use Phalcon\Forms\Element\AbstractElement;

class Button extends AbstractElement
{
    /**
     * Renders the element widget
     *
     * @param array|null $attributes
     * @return string
     */
    public function render(array $attributes = null): string
    {
        $value = is_string($this->getValue()) ? json_decode($this->getValue(), true) : $this->getValue();

        return $this->getForm()->view->getPartial('@kikcms/fields/button', [
            'value' => $value,
            'name'  => $this->getName()
        ]);
    }
}