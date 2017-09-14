<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;

class HtmlField extends Field
{
    /** @var string */
    private $content;

    /** @var string */
    private $label;

    /**
     * @param string $label
     * @param string $content
     */
    public function __construct(string $label, string $content)
    {
        $this->setKey('html');

        $this->label   = $label;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return HtmlField
     */
    public function setContent(string $content): HtmlField
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
     * @return HtmlField
     */
    public function setLabel(string $label): HtmlField
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