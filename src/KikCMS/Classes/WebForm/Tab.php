<?php

namespace KikCMS\Classes\WebForm;


/**
 * Represents a tab of a form
 */
class Tab
{
    /** @var string */
    private $name;

    /** @var Field[] */
    private $fields;

    /**
     * @param string $name
     * @param array $fields
     */
    public function __construct(string $name, array $fields)
    {
        $this->name   = $name;
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param Field[] $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }
}