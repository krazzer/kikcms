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
            1  => '#FFFF00', //Geel
            2  => '#F7CE00', //Geel-oranje
            3  => '#FF9C00', //Oranje
            4  => '#FF7300', //Oranje-rood
            5  => '#D40000', //Rood
            6  => '#FF208F', //Roze
            7  => '#7B00B5', //Violet
            8  => '#00008D', //Donkerblauw
            9  => '#004AA5', //Blauw
            10 => '#00BEE6', //Lichtblauw
            11 => '#009700', //Groen
            12 => '#87F100', //Groengeel
            13 => '#FFFFFF', //Wit
            14 => '#808080', //Grijs
            15 => '#000000', //Zwart
            16 => '#8B4513', //Bruin
        ];

        return $this->getForm()->view->getPartial('@kikcms/fields/colorpicker', [
            'value'  => $this->getValue(),
            'name'   => $this->getName(),
            'colors' => $colors,
        ]);
    }
}