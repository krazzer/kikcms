<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;

use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\WebForm\Field;

/**
 * Contains where and how a certain DataForms' field should be stored and retrieved
 * This class is the default behaviour to store a fields' input in some other table than the one defined in the form
 * @deprecated Use RelationKeys instead
 */
class FieldStorage
{
    /** @var bool if true, the current language (see Filters) will be saved in the $languageCodeField */
    private $addLanguageCode = false;

    /** @var array */
    private $defaultValues = [];

    /** @var string|null can be used to force a field to be stored in a certain language, i.e. when a language switch
     * is not available or to force a certain language anyway */
    private $languageCode;

    /** @var string */
    private $languageCodeField = 'language_code';

    /** @var string|null */
    private $relatedField;

    /** @var string */
    private $relatedByField = DataTable::TABLE_KEY;

    /**
     * @return boolean
     */
    public function isAddLanguageCode(): bool
    {
        return $this->addLanguageCode;
    }

    /**
     * @param boolean $addLanguageCode
     * @return $this|FieldStorage
     */
    public function setAddLanguageCode(bool $addLanguageCode)
    {
        $this->addLanguageCode = $addLanguageCode;
        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultValues(): array
    {
        return $this->defaultValues;
    }

    /**
     * @param array $defaultValues
     * @return $this|FieldStorage
     */
    public function setDefaultValues(array $defaultValues): FieldStorage
    {
        $this->defaultValues = $defaultValues;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableModel(): string
    {
        return $this->tableModel;
    }

    /**
     * @param string $tableModel
     * @return $this|FieldStorage
     */
    public function setTableModel(string $tableModel): FieldStorage
    {
        $this->tableModel = $tableModel;
        return $this;
    }

    /**
     * @return Field
     */
    public function getField(): Field
    {
        return $this->field;
    }

    /**
     * @param Field $field
     * @return $this|FieldStorage
     */
    public function setField(Field $field): FieldStorage
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getRelatedField(): ?string
    {
        return $this->relatedField;
    }

    /**
     * @return null|string
     */
    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    /**
     * @param string $languageCode
     * @return $this|FieldStorage
     */
    public function setLanguageCode(string $languageCode): FieldStorage
    {
        $this->languageCode = $languageCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageCodeField(): string
    {
        return $this->languageCodeField;
    }

    /**
     * @param string $languageCodeField
     * @return $this|FieldStorage
     */
    public function setLanguageCodeField(string $languageCodeField): FieldStorage
    {
        $this->languageCodeField = $languageCodeField;
        return $this;
    }

    /**
     * @param string $relatedField
     * @return $this|FieldStorage
     */
    public function setRelatedField(string $relatedField): FieldStorage
    {
        $this->relatedField = $relatedField;
        return $this;
    }

    /**
     * @return string
     */
    public function getRelatedByField(): string
    {
        return $this->relatedByField;
    }

    /**
     * @param string $relatedByField
     * @return $this|FieldStorage
     */
    public function setRelatedByField(string $relatedByField): FieldStorage
    {
        $this->relatedByField = $relatedByField;
        return $this;
    }
}