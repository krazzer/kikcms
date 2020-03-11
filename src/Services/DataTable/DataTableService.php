<?php
declare(strict_types=1);

namespace KikCMS\Services\DataTable;


use Exception;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\WebForm\DataForm\StorageData;
use KikCMS\Services\Util\QueryService;
use Monolog\Logger;
use KikCMS\Classes\Phalcon\Injectable;

/**
 * @property QueryService $queryService
 */
class DataTableService extends Injectable
{
    /**
     * @param DataTable $dataTable
     * @param StorageData $storageData
     */
    public function addDisplayOrderToStorageData(DataTable $dataTable, StorageData $storageData)
    {
        if ($dataTable->isSortableNewFirst()) {
            $storageData->addAdditionalInputValue($dataTable->getSortableField(), 1);
            $this->rearrangeService->makeRoomForFirst($dataTable->getModel());
        } else {
            $newValue = $this->rearrangeService->getMax($dataTable->getModel()) + 1;
            $storageData->addAdditionalInputValue($dataTable->getSortableField(), $newValue);
        }
    }

    /**
     * @param int $fileId
     * @param DataTable $dataTable
     * @return int|null
     */
    public function addImageDirectly(int $fileId, DataTable $dataTable): ?int
    {
        $model      = $dataTable->getModel();
        $imageField = $dataTable->getDirectImageField();
        $object     = $this->modelService->getObject($model);

        $storageData = (new StorageData)
            ->addFormInputValue($imageField, $fileId)
            ->setTable($model)
            ->setObject($object);

        if ($dataTable->isSortable()) {
            $this->dataTableService->addDisplayOrderToStorageData($dataTable, $storageData);
        }

        $this->storageService->setStorageData($storageData);

        $success = $this->storageService->store();

        if ($success) {
            return $storageData->getEditId();
        }

        return null;
    }

    /**
     * @param DataTable $dataTable
     * @param int $id
     * @return int
     */
    public function getPageById(DataTable $dataTable, int $id): int
    {
        if ($fromAlias = $this->queryService->getFromAlias($dataTable->getQuery())) {
            $column = $fromAlias . '.' . DataTable::TABLE_KEY;
        } else {
            $column = DataTable::TABLE_KEY;
        }

        $idsQuery = (clone $dataTable->getQuery())->columns([$column]);

        try {
            $index = array_search($id, $this->dbService->getValues($idsQuery));
            $limit = $dataTable->getLimit();

            return (($index - ($index % $limit)) / $limit) + 1;
        } catch (Exception $exception) {
            $this->logger->log(Logger::NOTICE, $exception);
        }

        return $dataTable->getFilters()->getPage();
    }

    /**
     * @param DataTable $dataTable
     * @return string
     */
    public function getRenderedForm(DataTable $dataTable): string
    {
        if ($dataTable->getFilters()->getEditId() === null) {
            return $dataTable->renderAddForm();
        }

        return $dataTable->renderEditForm();
    }

    /**
     * Handles actions that happen after a new object is stored
     *
     * @param DataTable $dataTable
     */
    public function handleNewObject(DataTable $dataTable)
    {
        $editId          = $dataTable->getForm()->getFilters()->getEditId();
        $hasTempParentId = $dataTable->getFilters()->hasTempParentEditId();

        // go to the page where the new id sits
        $page = $this->getPageById($dataTable, $editId);
        $dataTable->getFilters()->setPage($page);

        // if the datatable has a unsaved parent, cache the new id
        if ($dataTable->getFilters()->getParentRelationKey() && $hasTempParentId) {
            $dataTable->cacheNewId($editId);
        }
    }

    /**
     * @param DataTable $dataTable
     * @return array
     */
    public function validateDirectImage(DataTable $dataTable): array
    {
        foreach ($dataTable->getDirectImageValidators() as $validator) {
            $this->validation->add('fileId', $validator);
        }

        $messageArray = [];

        $messages = $this->validation->validate($this->request->getPost());

        foreach ($messages as $message){
            $messageArray[] = str_replace(':label ', '', $message->getMessage());
        }

        return $messageArray;
    }
}