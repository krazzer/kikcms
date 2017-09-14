<?php

namespace KikCMS\Classes\Page;


class Template
{
    /** @var string */
    private $key;

    /** @var string */
    private $name;

    /** @var array */
    private $fields = [];

    /**
     * @param string $key
     * @param string $name
     * @param array $fields
     */
    public function __construct(string $key, string $name, array $fields = [])
    {
        $this->key    = $key;
        $this->name   = $name;
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Template
     */
    public function setKey(string $key): Template
    {
        $this->key = $key;
        return $this;
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
     * @return Template
     */
    public function setName(string $name): Template
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     * @return Template
     */
    public function setFields(array $fields): Template
    {
        $this->fields = $fields;
        return $this;
    }
}