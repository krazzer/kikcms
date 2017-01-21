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
     * @param array $input
     * @param mixed $editId
     */
    public function store(array $input, $editId)
    {
        $dataTable    = $this->field->getDataTable();
        $keysToUpdate = $dataTable->getCachedNewIds();

        foreach ($keysToUpdate as $newId) {
            $model       = $dataTable->getModel();
            $relationKey = $dataTable->getParentRelationKey();

            $this->dbService->update($model, [$relationKey => $editId], ['id' => $newId, $relationKey => 0]);
        }
    }
}