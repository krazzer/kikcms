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
     * @param array $input
     * @param mixed $editId
     */
    public function store(array $input, $editId)
    {
        /** @var MultiCheckboxField $element */
        $element = $this->field->getElement();
        $key     = $this->field->getKey();

        $table       = $this->getTable();
        $relationKey = $this->getRelationKey();

        $ids   = array_keys($element->getOptions());
        $where = $relationKey . ' = ' . $editId . ' AND ' . $key . ' IN (' . implode(',', $ids) . ')';

        $this->db->delete($table, $where);

        if ( ! isset($input[$key])) {
            return;
        }

        foreach ($input[$key] as $id) {
            $this->db->insert($table, [$editId, $id], [$relationKey, $key]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getValue($id)
    {
        $fieldKey = $this->field->getKey();

        $altQuery = new Builder();
        $altQuery
            ->columns($fieldKey)
            ->addFrom($this->getTableModel())
            ->andWhere($this->getRelationKey() . ' = ' . $id);

        $rows   = $altQuery->getQuery()->execute()->toArray();
        $values = [];

        foreach ($rows as $row) {
            $values[] = $row[$fieldKey];
        }

        return $values;
    }
}