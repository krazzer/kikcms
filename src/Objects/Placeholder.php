<?php


namespace KikCMS\Objects;


class Placeholder
{
    /** @var int */
    private $id;

    /** @var string */
    private $placeholder;

    /** @var array */
    private $arguments;

    /**
     * @param int $id
     * @param string $placeholder
     * @param array $arguments
     */
    public function __construct(int $id, string $placeholder, array $arguments = [])
    {
        $this->id          = $id;
        $this->placeholder = $placeholder;
        $this->arguments   = $arguments;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Placeholder
     */
    public function setId(int $id): Placeholder
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     * @return Placeholder
     */
    public function setArguments(array $arguments): Placeholder
    {
        $this->arguments = $arguments;
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