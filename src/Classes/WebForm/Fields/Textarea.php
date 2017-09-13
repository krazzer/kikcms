<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;

class Textarea extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_TEXTAREA;
    }

    /**
     * Shortcut to set the textarea's height
     *
     * @param int $rows
     * @return $this|Textarea
     */
    public function rows(int $rows)
    {
        $style = $this->getElement()->getAttribute('style');
        $this->getElement()->setAttribute('style', $style . 'height: ' . (($rows * 19) + 16) . 'px;');

        return $this;
    }
}