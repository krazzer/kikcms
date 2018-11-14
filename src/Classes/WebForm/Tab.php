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

    /** @var string|null */
    private $key;

    /**
     * @param string $name
     * @param Field[] $fields
     */
    public function __construct(string $name, array $fields)
    {
        $this->name = $name;

        $this->fieldMap = new FieldMap();

        foreach ($fields as $field) {
            $key = $field->getKey() ?: $field->getElement()->getName();
            $this->fieldMap->add($field, $key);
        }
    }

    /**
     * @param Field $field
     * @return $this
     */
    public function addField(Field $field): Tab
    {
        $this->fieldMap->add($field, $field->getKey());

        $field->setTab($this);

        return $this;
    }

    /**
     * @param Field $field
     * @param string $targetKey
     * @return $this
     */
    public function addFieldAfter(Field $field, string $targetKey): Tab
    {
        $this->fieldMap->addAfter($field, $field->getKey(), $targetKey);

        $field->setTab($this);

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
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return FieldMap
     */
    public function getFieldMap(): FieldMap
    {
        return $this->fieldMap;
    }

    /**
     * @param FieldMap $fieldMap
     */
    public function setFieldMap(FieldMap $fieldMap)
    {
        $this->fieldMap = $fieldMap;
    }

    /**
     * @return null|string
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @param null|string $key
     * @return Tab
     */
    public function setKey(?string $key): Tab
    {
        $this->key = $key;
        return $this;
    }
}