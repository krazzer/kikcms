<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;

class Button extends Field
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
     * @return $this|Button
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
     * @return $this|Button
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
     * @return $this|Button
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
     * @return Button
     */
    public function setLabel(string $label): Button
    {
        $this->label = $label;
        return $this;
    }
}