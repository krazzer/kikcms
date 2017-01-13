<?php

namespace KikCMS\Classes\WebForm\DataForm;

use KikCMS\Classes\Model\Model;
use KikCMS\Classes\WebForm\Field;
use Phalcon\Di\Injectable;

/**
 * Manages where and how a certain DataForms' field should be stored and retrieved
 */
class FieldStorage extends Injectable
{
    /** @var string */
    protected $tableModel;

    /** @var Field */
    protected $field;

    /** @var string */
    protected $relationKey;

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
     * @param $id
     * @return mixed
     */
    public function getValue($id)
    {
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
     * @param array $input
     * @param $editId
     */
    public function store(array $input, $editId)
    {
    }
}