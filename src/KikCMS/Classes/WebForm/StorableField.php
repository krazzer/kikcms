<?php

namespace KikCMS\Classes\WebForm;
use KikCMS\Classes\Model\Model;

/**
 * Represents a DataForms' field which value will be stored somewhere
 */
class StorableField extends Field
{
    /** @var FieldStorage */
    private $fieldStorage;

    /**
     * Shortcut for setting fieldStorage
     *
     * @param string $table
     * @param string $relationKey
     *
     * @return $this|StorableField
     */
    public function table(string $table, string $relationKey)
    {
        $fieldStorage = new FieldStorage();
        $fieldStorage->setField($this);
        $fieldStorage->setTable($table);
        $fieldStorage->setRelationKey($relationKey);

        $this->setFieldStorage($fieldStorage);

        return $this;
    }

    /**
     * @return FieldStorage
     */
    public function getFieldStorage(): FieldStorage
    {
        return $this->fieldStorage;
    }

    /**
     * @param FieldStorage $fieldStorage
     * @return $this|StorableField
     */
    public function setFieldStorage(FieldStorage $fieldStorage): StorableField
    {
        $this->fieldStorage = $fieldStorage;
        return $this;
    }

    /**
     * Return the actual table
     *
     * @return string
     */
    public function getTable(): string
    {
        $tableModel = $this->fieldStorage->getTable();

        /** @var Model $model */
        $model = new $tableModel();
        return $model->getSource();
    }

    /**
     * Retrieve the value stored elsewhere by the given relation id
     *
     * @param $id
     * @return mixed
     */
    public function getValue($id)
    {
        return $this->fieldStorage->getValue($id);
    }

    /**
     * Return the table model
     *
     * @return string
     */
    public function getTableModel(): string
    {
        return $this->fieldStorage->getTable();
    }

    /**
     * @return bool
     */
    public function isStoredElsewhere(): bool
    {
        return $this->fieldStorage != null;
    }
}