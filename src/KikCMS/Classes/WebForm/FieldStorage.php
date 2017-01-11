<?php

namespace KikCMS\Classes\WebForm;

use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Resultset\Simple;

/**
 * Manages where and how a certain DataForms' field should be stored and retrieved
 */
class FieldStorage extends Injectable
{
    /** @var string */
    private $table;

    /** @var Field */
    private $field;

    /** @var string */
    private $relationKey;

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     * @return FieldStorage
     */
    public function setTable(string $table): FieldStorage
    {
        $this->table = $table;
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
        $fieldKey = $this->field->getElement()->getName();

        switch($this->field->getType())
        {
            case Field::TYPE_MULTI_CHECKBOX:

                $altQuery = new Builder();
                $altQuery
                    ->columns($fieldKey)
                    ->addFrom($this->getTable())
                    ->andWhere($this->getRelationKey() . ' = ' . $id);

                /** @var Simple $results */
                $results = $altQuery->getQuery()->execute();
                $rows    = $results->toArray();
                $values  = [];

                foreach ($rows as $row) {
                    $values[] = $row[$fieldKey];
                }

                return $values;
            break;

            default:
                return null;
            break;
        }
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
}