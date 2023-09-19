<?php declare(strict_types=1);

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

    /** @var bool */
    private $targetBlank = false;

    /**
     * @param string $key
     * @param string $label
     * @param string $info
     * @param string $buttonLabel
     * @param string $route
     */
    public function __construct(string $key, string $label, string $info, string $buttonLabel, string $route)
    {
        $this->key   = $key;
        $this->info  = $info;
        $this->label = $label;
        $this->route = $route;

        $this->buttonLabel = $buttonLabel;
    }

    /**
     * @inheritdoc
     */
    public function getType(): ?string
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
    public function setInfo(string $info): ButtonField|static
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
    public function setButtonLabel(string $buttonLabel): ButtonField|static
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
    public function setRoute(string $route): ButtonField|static
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

    /**
     * @return bool
     */
    public function isTargetBlank(): bool
    {
        return $this->targetBlank;
    }

    /**
     * @param bool $targetBlank
     * @return ButtonField
     */
    public function setTargetBlank(bool $targetBlank = true): ButtonField
    {
        $this->targetBlank = $targetBlank;
        return $this;
    }
}