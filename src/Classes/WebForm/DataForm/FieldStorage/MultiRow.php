<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;


use KikCMS\Classes\DbService;
use KikCMS\Classes\WebForm\DataForm\FieldStorage;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Saves the value in multiple rows, can only be used by fields that produce multiple value,
 * like MultiCheckbox and SelectDataTable
 *
 * @property DbService $dbService
 */
class MultiRow extends FieldStorage
{
    /**
     * @inheritdoc
     */
    public function store($value, $relationId, $languageCode = null)
    {
        if( ! is_array($value)){
            throw new \InvalidArgumentException(static::class . ' can only store array values');
        }

        $key = $this->field->getKey();

        $table       = $this->getTableModel();
        $relationKey = $this->getRelationKey();

        $where = [$relationKey => $relationId] + $this->getDefaultValues();

        if ($this->addLanguageCode) {
            $where[$this->getLanguageCodeField()] = $languageCode;
        }

        $this->dbService->delete($table, $where);

        foreach ($value as $id) {
            $insert = $where;
            $insert[$key] = $id;

            $this->dbService->insert($table, $insert);
        }
    }

    /**
     * @inheritdoc
     */
    public function getValue($relationId, $languageCode = null)
    {
        $query = (new Builder())
            ->columns($this->field->getKey())
            ->addFrom($this->getTableModel())
            ->andWhere($this->getRelationKey() . ' = ' . $relationId);

        if ($this->addLanguageCode) {
            $query->andWhere($this->getLanguageCodeField() . ' = :langCode:', [
                'langCode' => $languageCode
            ]);
        }

        return $this->dbService->getValues($query);
    }
}