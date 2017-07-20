<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;


use KikCMS\ObjectLists\FieldMap;

/**
 * Contains formatted data for storage
 */
class StorageData
{
    /** @var int|null */
    private $editId;

    /** @var int|null */
    private $parentEditId;

    /** @var FieldMap */
    private $fieldMap;

    /** @var array */
    private $input = [];

    /** @var string|null */
    private $languageCode = null;

    /** @var string */
    private $table;

    /** @var array */
    private $events;

    /**
     * @return int|null
     */
    public function getEditId()
    {
        return $this->editId;
    }

    /**
     * @param int|null $editId
     * @return StorageData|$this
     */
    public function setEditId($editId): StorageData
    {
        $this->editId = $editId;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * @param null|string $languageCode
     * @return StorageData|$this
     */
    public function setLanguageCode($languageCode): StorageData
    {
        $this->languageCode = $languageCode;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getParentEditId()
    {
        return $this->parentEditId;
    }

    /**
     * @param int|null $parentEditId
     * @return StorageData|$this
     */
    public function setParentEditId($parentEditId): StorageData
    {
        $this->parentEditId = $parentEditId;
        return $this;
    }

    /**
     * Returns an array with only the fields that are to be saved in the main table
     *
     * @return array
     */
    public function getMainInput(): array
    {
        $input = $this->input;

        //todo: this is wrong, fields like translations should be able to be inserted even though they are marked as stored elsewhere
        foreach ($this->fieldMap as $key => $field){
            if($field->getStorage()){
                unset($input[$key]);
            }
        }

        return $input;
    }

    /**
     * @return array
     */
    public function getInput(): array
    {
        return $this->input;
    }

    /**
     * @param array $input
     * @return StorageData|$this
     */
    public function setInput(array $input): StorageData
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @return StorageData|$this
     */
    public function addValue(string $key, $value): StorageData
    {
        $this->input[$key] = $value;
        return $this;
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
     * @return StorageData|$this
     */
    public function setFieldMap(FieldMap $fieldMap): StorageData
    {
        $this->fieldMap = $fieldMap;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     * @return StorageData|$this
     */
    public function setTable(string $table): StorageData
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTempParentEditId(): bool
    {
        return $this->parentEditId === 0;
    }

    /**
     * @param string $field
     * @return bool
     */
    public function hasValue(string $field): bool
    {
        return array_key_exists($field, $this->input);
    }

    /**
     * @param string $field
     * @return mixed
     */
    public function getValue(string $field)
    {
        if( ! array_key_exists($field, $this->input)){
            return null;
        }

        return $this->input[$field];
    }

    /**
     * @param string $field
     * @param $value
     * @return StorageData|$this
     */
    public function setValue(string $field, $value): StorageData
    {
        $this->input[$field] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param array $events
     * @return StorageData
     */
    public function setEvents(array $events): StorageData
    {
        $this->events = $events;
        return $this;
    }
}