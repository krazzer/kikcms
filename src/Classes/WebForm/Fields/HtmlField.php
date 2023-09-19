<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;

class HtmlField extends Field
{
    /** @var string */
    private $content;

    /** @var string|null */
    private $label;

    /**
     * @param string $key
     * @param string|null $label
     * @param string $content
     */
    public function __construct(string $key, ?string $label, string $content)
    {
        $this->label   = $label;
        $this->content = $content;
        $this->key     = $key;
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
     * @return string|null
     */
    public function getLabel(): ?string
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
     * @return string|null
     */
    public function getType(): ?string
    {
        return Field::TYPE_HTML;
    }
}