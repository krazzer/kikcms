<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;


use KikCMS\Classes\DbService;
use KikCMS\Classes\WebForm\DataForm\FieldStorage;
use KikCMS\Classes\WebForm\Fields\DataTableField;

/**
 * DataTable fields aren't actually stored here, they are stored right away, however, when the parent form is not yet
 * saved, the Sub DataTable's entries are referring to a non-existing parent (0) which are updated here
 *
 * @property DbService $dbService
 */
class DataTable extends FieldStorage
{
    /** @var DataTableField */
    protected $field;

    /**
     * @inheritdoc
     */
    public function store($value, $relationId, $languageCode = null)
    {
        $dataTable    = $this->field->getDataTable();
        $keysToUpdate = $dataTable->getCachedNewIds();

        foreach ($keysToUpdate as $newId) {
            $model       = $dataTable->getModel();
            $relationKey = $dataTable->getParentRelationKey();

            $this->dbService->update($model, [$relationKey => $relationId], ['id' => $newId, $relationKey => 0]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getValue($relationId, $languageCode = null)
    {
        // handled by the DataTable itself
    }
}