<?php

namespace KikCMS\Classes\WebForm;
use KikCMS\ObjectLists\FieldMap;


/**
 * Represents a tab of a form
 */
class Tab
{
    /** @var string */
    private $name;

    /** @var FieldMap */
    private $fieldMap;

    /**
     * @param string $name
     * @param array $fields
     */
    public function __construct(string $name, array $fields)
    {
        $this->name     = $name;
        $this->fieldMap = $fields;
    }

    public function addField(Field $field)
    {
        $this->fieldMap[] = $field;

        $field->setTab($this);
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
    public function getFieldMap(): array
    {
        return $this->fieldMap;
    }

    /**
     * @param Field[] $fieldMap
     */
    public function setFieldMap(array $fieldMap)
    {
        $this->fieldMap = $fieldMap;
    }
}