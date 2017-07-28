<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;


use KikCMS\Classes\WebForm\Field;
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

    /** @var array [formFieldKey => value] */
    private $formInput = [];

    /** @var array additional input to be stored in the main table, added on top of form input [tableColumn => value] */
    private $additionalInput = [];

    /** @var string|null */
    private $languageCode = null;

    /** @var string */
    private $table;

    /** @var array */
    private $events;

    /**
     * @return null|int
     */
    public function getEditId(): ?int
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
    public function getLanguageCode(): ?string
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
    public function getParentEditId(): ?int
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
     * @return array [tableColumn => value]
     */
    public function getMainInput(): array
    {
        $mainInput = [];

        /** @var Field $field */
        foreach ($this->fieldMap as $key => $field){
            if($field->getStorage()){
                continue;
            }

            if( ! array_key_exists($key, $this->formInput)){
                continue;
            }

            $mainInput[$field->getColumn()] = $this->formInput[$key];
        }

        $mainInput += $this->additionalInput;

        return $mainInput;
    }

    /**
     * @return array
     */
    public function getFormInput(): array
    {
        return $this->formInput;
    }

    /**
     * @param array $formInput
     * @return StorageData|$this
     */
    public function setFormInput(array $formInput): StorageData
    {
        $this->formInput = $formInput;
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @return StorageData|$this
     */
    public function addFormInputValue(string $key, $value): StorageData
    {
        $this->formInput[$key] = $value;
        return $this;
    }

    /**
     * @param string $column
     * @param $value
     * @return StorageData|$this
     */
    public function addAdditionalInputValue(string $column, $value): StorageData
    {
        $this->additionalInput[$column] = $value;
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
     * @param string $key
     * @return bool
     */
    public function formInputValueExists(string $key): bool
    {
        return array_key_exists($key, $this->formInput);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getFormInputValue(string $key)
    {
        if( ! array_key_exists($key, $this->formInput)){
            return null;
        }

        return $this->formInput[$key];
    }

    /**
     * @param string $column
     * @return mixed
     */
    public function getAdditionalInputValue(string $column)
    {
        if( ! array_key_exists($column, $this->additionalInput)){
            return null;
        }

        return $this->additionalInput[$column];
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

    /**
     * @return array
     */
    public function getAdditionalInput(): array
    {
        return $this->additionalInput;
    }

    /**
     * @param array $additionalInput
     * @return StorageData
     */
    public function setAdditionalInput(array $additionalInput): StorageData
    {
        $this->additionalInput = $additionalInput;
        return $this;
    }
}