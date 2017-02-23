<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;


use KikCMS\Classes\DbService;
use KikCMS\Classes\WebForm\DataForm\FieldStorage;
use KikCMS\Classes\WebForm\Fields\DataTableField;

/** @property DbService $dbService */
class DataTable extends FieldStorage
{
    /** @var DataTableField */
    protected $field;

    /**
     * @inheritdoc
     */
    public function store($value, $relationId)
    {
        $dataTable    = $this->field->getDataTable();
        $keysToUpdate = $dataTable->getCachedNewIds();

        foreach ($keysToUpdate as $newId) {
            $model       = $dataTable->getModel();
            $relationKey = $dataTable->getParentRelationKey();

            $this->dbService->update($model, [$relationKey => $relationId], ['id' => $newId, $relationKey => 0]);
        }
    }

    public function getValue()
    {
    }
}