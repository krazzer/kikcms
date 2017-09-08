<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;

class Html extends Field
{
    /** @var string */
    private $content;

    /** @var string */
    private $label;

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return Html
     */
    public function setContent(string $content): Html
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return Html
     */
    public function setLabel(string $label): Html
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_HTML;
    }
}