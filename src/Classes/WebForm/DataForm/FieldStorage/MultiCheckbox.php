<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;


use KikCMS\Classes\WebForm\DataForm\FieldStorage;
use KikCMS\Classes\Phalcon\FormElements\MultiCheck as MultiCheckboxField;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Manages where and how a certain DataForms' field should be stored and retrieved
 */
class MultiCheckbox extends FieldStorage
{
    /**
     * @inheritdoc
     */
    public function store($value, $relationId, $languageCode = null)
    {
        /** @var MultiCheckboxField $element */
        $element = $this->field->getElement();
        $key     = $this->field->getKey();

        $table       = $this->getTable();
        $relationKey = $this->getRelationKey();

        $ids   = array_keys($element->getOptions());
        $where = $relationKey . ' = ' . $relationId . ' AND ' . $key . ' IN (' . implode(',', $ids) . ')';

        $this->db->delete($table, $where);

        foreach ($value as $id) {
            $this->db->insert($table, [$relationId, $id], [$relationKey, $key]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getValue($relationId, $languageCode = null)
    {
        $fieldKey = $this->field->getKey();

        $altQuery = new Builder();
        $altQuery
            ->columns($fieldKey)
            ->addFrom($this->getTableModel())
            ->andWhere($this->getRelationKey() . ' = ' . $relationId);

        $rows   = $altQuery->getQuery()->execute()->toArray();
        $values = [];

        foreach ($rows as $row) {
            $values[] = $row[$fieldKey];
        }

        return $values;
    }
}