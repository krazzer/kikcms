<?php declare(strict_types=1);


namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;

class Header extends Field
{
    /** @var string */
    private $label;

    /**
     * @param string $label
     */
    public function __construct(string $label)
    {
        $this->key   = 'header_' . uniqid();
        $this->label = $label;
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
     * @return Header
     */
    public function setLabel(string $label): Header
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_HEADER;
    }
}