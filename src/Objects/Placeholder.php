<?php


namespace KikCMS\Objects;


abstract class Placeholder
{
    /** @var string */
    private $key;

    /** @var string */
    private $placeholder;

    /**
     * @param string $key
     * @param string $placeholder
     * @param array $arguments
     */
    public function __construct(string $key, string $placeholder, array $arguments = [])
    {
        $this->key         = $key;
        $this->placeholder = $placeholder;

        $this->mapArguments($arguments);
    }

    abstract function mapArguments(array $arguments);

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Placeholder
     */
    public function setKey(string $key): Placeholder
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    /**
     * @param string $placeholder
     * @return Placeholder
     */
    public function setPlaceholder(string $placeholder): Placeholder
    {
        $this->placeholder = $placeholder;
        return $this;
    }
}