<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;

class ButtonField extends Field
{
    /** @var string */
    private $info;

    /** @var string */
    private $buttonLabel;

    /** @var string */
    private $route;

    /** @var string */
    private $label;

    /**
     * @param string $label
     * @param string $info
     * @param string $buttonLabel
     * @param string $route
     */
    public function __construct(string $label, string $info, string $buttonLabel, string $route)
    {
        $this->key = 'button';

        $this->info  = $info;
        $this->label = $label;
        $this->route = $route;

        $this->buttonLabel = $buttonLabel;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_BUTTON;
    }

    /**
     * @return string
     */
    public function getInfo(): string
    {
        return $this->info;
    }

    /**
     * @param string $info
     * @return $this|ButtonField
     */
    public function setInfo(string $info)
    {
        $this->info = $info;
        return $this;
    }

    /**
     * @return string
     */
    public function getButtonLabel(): string
    {
        return $this->buttonLabel;
    }

    /**
     * @param string $buttonLabel
     * @return $this|ButtonField
     */
    public function setButtonLabel(string $buttonLabel)
    {
        $this->buttonLabel = $buttonLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param string $route
     * @return $this|ButtonField
     */
    public function setRoute(string $route)
    {
        $this->route = $route;
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
     * @return ButtonField
     */
    public function setLabel(string $label): ButtonField
    {
        $this->label = $label;
        return $this;
    }
}