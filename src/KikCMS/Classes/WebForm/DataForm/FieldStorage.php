<?php

namespace KikCMS\Classes\WebForm\DataForm;

use KikCMS\Classes\DbService;
use KikCMS\Classes\Model\Model;
use KikCMS\Classes\WebForm\Field;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Manages where and how a certain DataForms' field should be stored and retrieved
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
     */
    public function setAddLanguageCode(bool $addLanguageCode)
    {
        $this->addLanguageCode = $addLanguageCode;
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
     * @return FieldStorage
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
     * @return FieldStorage
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
     * Retrieve the value stored by the given relation id
     *
     * @param $relationId
     * @param string $languageCode
     *
     * @return mixed
     */
    public function getValue($relationId, $languageCode = 'nl')
    {
        $existsQuery = new Builder();
        $existsQuery->addFrom($this->tableModel);
        $existsQuery->columns($this->field->getKey());
        $existsQuery->where($this->relationKey . ' = ' . $relationId);

        if ($this->addLanguageCode) {
            $existsQuery->andWhere($this->languageCodeField . ' = ' . $this->db->escapeString($languageCode));
        }

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
     * @return FieldStorage
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
     */
    public function store($value, $relationId, $languageCode = 'nl')
    {
        $set   = [$this->field->getKey() => $value];
        $where = [$this->getRelationKey() => $relationId];

        if ($this->addLanguageCode) {
            $where[$this->languageCodeField] = $languageCode;
        }

        if ($this->getValue($relationId, $languageCode)) {
            $this->dbService->update($this->getTableModel(), $set, $where);
        } else {
            $this->dbService->insert($this->getTableModel(), $set + $where);
        }
    }
}