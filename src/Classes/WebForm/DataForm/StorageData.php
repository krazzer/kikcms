<?php

namespace KikCMS\Classes\WebForm\DataForm;


use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\Fields\ButtonField;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Classes\WebForm\Fields\HtmlField;
use KikCMS\ObjectLists\FieldMap;
use KikCmsCore\Classes\Model;

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

    /** @var Model */
    private $object;

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
     * Returns an array with only the fields that are to be saved, except DataTableFields
     *
     * @return array [tableColumn => value]
     */
    public function getMainInput(): array
    {
        $mainInput = [];

        /** @var Field $field */
        foreach ($this->fieldMap as $key => $field) {
            if ($field->isDontStore() || $field instanceof DataTableField || $field instanceof ButtonField ||
                $field instanceof HtmlField) {
                continue;
            }

            if ( ! array_key_exists($key, $this->formInput)) {
                $this->formInput[$key] = null;
            }

            $mainInput[$key] = $this->formInput[$key];
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
     * @return Model
     */
    public function getObject(): Model
    {
        return $this->object;
    }

    /**
     * @param Model $object
     * @return StorageData
     */
    public function setObject(Model $object): StorageData
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @return DataTableField[]
     */
    public function getDataTableFieldMap(): array
    {
        $dataTableFields = [];

        foreach ($this->fieldMap as $key => $field) {
            if ($field instanceof DataTableField) {
                $dataTableFields[$key] = $field;
            }
        }

        return $dataTableFields;
    }
}