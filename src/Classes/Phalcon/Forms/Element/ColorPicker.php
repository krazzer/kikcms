<?php

namespace KikCMS\Classes\Phalcon\Forms\Element;

use Phalcon\Forms\Element\AbstractElement;

class ColorPicker extends AbstractElement
{
    /**
     * Renders the element widget
     *
     * @param array|null $attributes
     * @return string
     */
    public function render(array $attributes = null): string
    {
        $colors = [
            '#ffff00', //Geel
            '#f7ce00', //Geel-oranje
            '#ff9c00', //Oranje
            '#ff7300', //Oranje-rood
            '#ff0000', //Rood
            '#ff208f', //Roze
            '#7b00b5', //Violet
            '#00008d', //Donkerblauw
            '#004aa5', //Blauw
            '#00bee6', //Lichtblauw
            '#009700', //Groen
            '#87f100', //Groengeel
            '#FFFFFF', //Wit
            '#808080', //Grijs
            '#000000', //Zwart
            '#8B4513', //Bruin
        ];

        return $this->getForm()->view->getPartial('@kikcms/fields/colorpicker', [
            'value'  => $this->getValue(),
            'name'   => $this->getName(),
            'colors' => json_encode($colors),
        ]);
    }
}