<?php

namespace KikCMS\Services\WebForm;

use Exception;
use InvalidArgumentException;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\WebForm\DataForm\StorageData;
use KikCMS\Classes\WebForm\Fields\KeyedDataTableField;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\Exceptions\ParentRelationKeyReferenceMissingException;
use KikCMS\Classes\WebForm\DataForm\Events\StoreEvent;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Services\TranslationService;
use Monolog\Logger;
use Phalcon\Di\Injectable;

/**
 * Service for handling a DataForms' Storage, using the StorageData object
 *
 * @property DbService $dbService
 * @property TranslationService $translationService
 * @property RelationKeyService $relationKeyService
 */
class StorageService extends Injectable
{
    /** @var StorageData */
    private $storageData;

    /**
     * @return StorageData
     */
    public function getStorageData(): StorageData
    {
        return $this->storageData;
    }

    /**
     * @param DataTableField $field
     * @param array $editData
     * @param int $editId
     * @return int
     * @throws ParentRelationKeyReferenceMissingException
     * @deprecated Use RelationKeys instead
     */
    public function getRelatedValueForField(DataTableField $field, array $editData, int $editId): int
    {
        if ( ! $referencedColumn = $field->getDataTable()->getParentRelationKeyReference()) {
            return $editId;
        }

        if ( ! array_key_exists($referencedColumn, $editData)) {
            throw new ParentRelationKeyReferenceMissingException($referencedColumn, get_class($field->getForm()));
        }

        return $editData[$referencedColumn];
    }

    /**
     * @param StorageData $storageData
     * @return StorageService|$this
     */
    public function setStorageData(StorageData $storageData): StorageService
    {
        $this->storageData = $storageData;
        return $this;
    }

    /**
     * Store a DataForm's input data wrapped inside a StorageData object
     *
     * @return bool
     */
    public function store(): bool
    {
        if ( ! $this->storageData) {
            throw new InvalidArgumentException("property storageData must be set");
        }

        $this->db->begin();

        try {
            $this->executeBeforeStoreEvents();
            $this->storeMain();
            $this->storePostMain();
        } catch (Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception);
            $this->db->rollback();
            return false;
        }

        $success = $this->db->commit();

        if ($success) {
            $this->removeSubDataTableTemporaryKeysCache();
        }

        return $success;
    }

    /**
     * If a temporary key is inserted, fk checks needs to be disabled for insert
     */
    private function disableForeignKeysForTempKeys()
    {
        if ($this->storageData->hasTempParentEditId()) {
            $this->dbService->setForeignKeyChecks(false);
        }
    }

    /**
     * If a temporary key is inserted, fk checks needs to be re-enabled after insert
     */
    private function enableForeignKeysForTempKeys()
    {
        if ($this->storageData->hasTempParentEditId()) {
            $this->dbService->setForeignKeyChecks(true);
        }
    }

    /**
     * @param string $eventType
     */
    private function executeEvents(string $eventType)
    {
        if ( ! array_key_exists($eventType, $this->storageData->getEvents())) {
            return;
        }

        $events = $this->storageData->getEvents()[$eventType];

        foreach ($events as $event) {
            $event($this->storageData);
        }
    }

    /**
     * Execute after the main insert/update is completed
     */
    private function executeAfterMainEvents()
    {
        $this->executeEvents(StoreEvent::BEFORE_MAIN_STORE);
    }

    /**
     * Execute after the main insert/update is completed
     */
    private function executeAfterStoreEvents()
    {
        $this->executeEvents(StoreEvent::AFTER_STORE);
    }

    /**
     * Execute after the main insert/update is completed
     */
    private function executeBeforeStoreEvents()
    {
        $this->executeEvents(StoreEvent::BEFORE_STORE);
    }

    /**
     * Execute after the main insert/update is completed
     */
    private function executeBeforeMainEvents()
    {
        $this->executeEvents(StoreEvent::BEFORE_MAIN_STORE);
    }

    /**
     * Store the main forms' table row
     */
    private function storeMain()
    {
        $mainInput = $this->storageData->getMainInput();
        $langCode  = $this->storageData->getLanguageCode();
        $object    = $this->storageData->getObject();

        // set objects' properties
        foreach ($mainInput as $key => $value) {
            if ($this->relationKeyService->isRelationKey($key)) {
                $this->relationKeyService->set($object, $key, $value, $langCode);
            } else {
                $object->$key = $this->dbService->toStorage($value);
            }
        }

        $this->executeBeforeMainEvents();

        if (property_exists($object, DataTable::TABLE_KEY)) {
            $object->save();
        } else {
            $this->disableForeignKeysForTempKeys();
            $object->save();
            $this->enableForeignKeysForTempKeys();
        }

        $this->storageData->setEditId((int) $object->id);

        $this->executeAfterMainEvents();
    }

    /**
     * Store data after the main forms' table row is inserted/updated
     */
    private function storePostMain()
    {
        $editId = $this->storageData->getEditId();
        $model  = $this->storageData->getTable();

        $editData = $this->dbService->getTableRowById($model, $editId);

        /** @var Field $field */
        foreach ($this->storageData->getFieldMap() as $key => $field) {
            // store old DataTableField
            if ($field instanceof DataTableField) {
                $dataTable = $field->getDataTable();

                $keysToUpdate = $dataTable->getCachedNewIds();
                $relatedField = $dataTable->getParentRelationKey();
                $model        = $dataTable->getModel();

                $relatedValue = $this->storageService->getRelatedValueForField($field, $editData, $editId);

                foreach ($keysToUpdate as $newId) {
                    $success = $this->dbService->update($model, [$relatedField => $relatedValue], [DataTable::TABLE_KEY => $newId, $relatedField => 0]);

                    if ( ! $success) {
                        continue;
                    }
                }
            }

            if ( ! $field instanceof KeyedDataTableField) {
                continue;
            }

            $dataTable = $field->getDataTable();

            $keysToUpdate = $dataTable->getCachedNewIds();
            $relatedModel = $dataTable->getModel();

            $object = $this->storageData->getObject();

            $relation = $object->getModelsManager()->getRelationByAlias($model, $key);

            $objectField  = $relation->getFields();
            $relatedField = $relation->getReferencedFields();

            foreach ($keysToUpdate as $newId) {
                $success = $this->dbService->update($relatedModel, [$relatedField => $object->$objectField],
                    [DataTable::TABLE_KEY => $newId, $relatedField => 0]);

                if ( ! $success) {
                    throw new Exception('KeyedDataTableField values not updated');
                }
            }
        }

        $this->executeAfterStoreEvents();
    }

    /**
     * Remove temporary keys cache file
     */
    private function removeSubDataTableTemporaryKeysCache()
    {
        /** @var DataTableField $field */
        foreach ($this->storageData->getFieldMap() as $field) {
            if ( ! in_array($field->getType(), [Field::TYPE_DATA_TABLE, Field::TYPE_KEYED_DATA_TABLE])) {
                continue;
            }

            $field->getDataTable()->removeNewIdCache();
        }
    }
}