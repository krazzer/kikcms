<?php

namespace KikCMS\Classes\WebForm\DataForm;

use KikCMS\Classes\DbService;
use KikCMS\Classes\Model\Model;
use KikCMS\Classes\WebForm\Field;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Manages where and how a certain DataForms' field should be stored and retrieved
 * This class is the default behaviour to store a fields' input in some other table than the one defined in the form
 *
 * @property DbService $dbService
 */
class FieldStorage extends Injectable
{
    /** @var string */
    protected $tableModel;

    /** @var Field */
    protected $field;

    /** @var string */
    protected $relationKey;

    /** @var bool */
    protected $addLanguageCode = false;

    /** @var array */
    private $defaultValues = [];

    /** @var string */
    private $languageCodeField = 'language_code';

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
     * @return string
     */
    public function getRelationKey(): string
    {
        return $this->relationKey;
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
     * Retrieve the value stored by the given relation id
     *
     * @param $relationId
     * @param string|null $languageCode
     * @return mixed
     */
    public function getValue($relationId, $languageCode = null)
    {
        $existsQuery = $this->getRelationQuery($relationId, $languageCode)
            ->columns($this->field->getTableField());

        return $this->dbService->getValue($existsQuery);
    }

    /**
     * Return the actual table
     *
     * @return string
     */
    public function getTable(): string
    {
        $tableModel = $this->getTableModel();

        /** @var Model $model */
        $model = new $tableModel();
        return $model->getSource();
    }

    /**
     * @param string $relationKey
     * @return $this|FieldStorage
     */
    public function setRelationKey(string $relationKey): FieldStorage
    {
        $this->relationKey = $relationKey;
        return $this;
    }

    /**
     * @param mixed $value
     * @param int $relationId
     * @param string $languageCode
     * @return bool|mixed
     */
    public function store($value, $relationId, $languageCode = null)
    {
        $value = $this->dbService->toStorage($value);

        $set   = [$this->field->getTableField() => $value];
        $where = $this->defaultValues + [$this->getRelationKey() => $relationId];

        if ($this->addLanguageCode) {
            $where[$this->languageCodeField] = $languageCode;
        }

        if ($this->relationRowExists($relationId, $languageCode)) {
            return $this->dbService->update($this->getTableModel(), $set, $where);
        }

        return $this->dbService->insert($this->getTableModel(), $set + $where);
    }

    /**
     * @param $relationId
     * @param string $languageCode
     * @return Builder
     */
    private function getRelationQuery($relationId, $languageCode = null)
    {
        $relationQuery = new Builder();
        $relationQuery->addFrom($this->tableModel);
        $relationQuery->where($this->relationKey . ' = ' . $relationId);

        foreach ($this->defaultValues as $field => $value) {
            $relationQuery->andWhere($field . ' = ' . $this->db->escapeString($value));
        }

        if ($this->addLanguageCode) {
            $relationQuery->andWhere($this->languageCodeField . ' = ' . $this->db->escapeString($languageCode));
        }

        return $relationQuery;
    }

    /**
     * @param $relationId
     * @param string $languageCode
     * @return bool
     */
    private function relationRowExists($relationId, $languageCode = null): bool
    {
        $existsQuery = $this->getRelationQuery($relationId, $languageCode);

        if (count($existsQuery->getQuery()->execute())) {
            return true;
        }

        return false;
    }
}