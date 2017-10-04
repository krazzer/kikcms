<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;

use Exception;
use InvalidArgumentException;
use KikCMS\Classes\DbService;
use KikCMS\Classes\Exceptions\ParentRelationKeyReferenceMissingException;
use KikCMS\Classes\WebForm\DataForm\Events\StoreEvent;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Services\TranslationService;
use Monolog\Logger;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Service for handling a DataForms' Storage, using the StorageData object
 *
 * @property DbService $dbService
 * @property FieldStorageService $fieldStorageService
 * @property TranslationService $translationService
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
     */
    public function getRelatedValueForField(DataTableField $field, array $editData, int $editId): int
    {
        if( ! $referencedColumn = $field->getDataTable()->getParentRelationKeyReference()) {
            return $editId;
        }

        if( ! array_key_exists($referencedColumn, $editData)){
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
            $this->storePreMain();
            $this->storeMain();
            $this->storePostMain();
        } catch (Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception);
            $this->db->rollback();
            return false;
        }

        return $this->db->commit();
    }

    /**
     * @param Field $field
     * @param $id
     * @param string|null $langCode
     * @param array $tableData
     * @return mixed
     * @throws Exception
     */
    public function retrieve(Field $field, $id, string $langCode = null, array $tableData)
    {
        switch (true) {
            case $field->getStorage() instanceof OneToOne:
                return $this->fieldStorageService->retrieveOneToOne($field, $tableData, $langCode);
            break;

            case $field->getStorage() instanceof OneToOneRef:
                return $this->fieldStorageService->retrieveOneToOneRef($field, $tableData);
            break;

            case $field->getStorage() instanceof ManyToMany:
                return $this->fieldStorageService->retrieveManyToMany($field, $id, $langCode);
            break;

            case $field->getStorage() instanceof Translation:
                return $this->fieldStorageService->retrieveTranslation($field, $id, $langCode);
            break;

            case $field->getStorage() instanceof OneToMany || $field->getStorage() instanceof None:
                return null;
            break;

            default:
                throw new Exception('Not implemented yet');
            break;
        }
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
     * @param FieldStorage $fieldStorage
     * @return null|string
     */
    private function getRelatedFieldValue(FieldStorage $fieldStorage): ?string
    {
        $query = (new Builder())
            ->from($this->storageData->getTable())
            ->columns($fieldStorage->getRelatedField())
            ->where('id = :id:', ['id' => $this->storageData->getEditId()]);

        return $this->dbService->getValue($query);
    }

    /**
     * Get a StorageValues list, containing data to insert for the OneToOneRef FieldStorage type
     *
     * @return array [relatedField => StorageValues]
     */
    private function getStorageValuesMap(): array
    {
        $fields = [];

        /** @var Field $field */
        foreach ($this->storageData->getFieldMap() as $key => $field) {
            $storage = $field->getStorage();

            if ( ! $storage instanceof OneToOneRef) {
                continue;
            }

            $relatedField = $storage->getRelatedField();

            if ( ! $value = $this->storageData->getFormInputValue($key)) {
                continue;
            }

            if ( ! array_key_exists($relatedField, $fields)) {
                $fields[$relatedField] = (new StorageValues())->setFieldStorage($storage);
            }

            $fields[$relatedField]->add($field->getColumn(), $value);

            foreach ($storage->getDefaultValues() as $defaultKey => $value) {
                $fields[$relatedField]->add($defaultKey, $value);
            }
        }

        return $fields;
    }

    /**
     * Store data before the main forms' table row is inserted/updated
     */
    private function storePreMain()
    {
        $this->executeBeforeStoreEvents();

        $storageValuesMap = $this->getStorageValuesMap();
        $storageData      = $this->storageData;

        /** @var StorageValues $storageValues */
        foreach ($storageValuesMap as $relatedField => $storageValues) {
            $fieldStorage = $storageValues->getFieldStorage();
            $valueMap     = $storageValues->getValueMap();
            $tableModel   = $fieldStorage->getTableModel();

            if ( ! $valueMap) {
                continue;
            }

            if ($storageData->getEditId() && $relatedFieldValue = $this->getRelatedFieldValue($fieldStorage)) {
                $this->dbService->update($tableModel, $valueMap, ['id' => $relatedFieldValue]);
            } else {
                $id = $this->dbService->insert($tableModel, $valueMap);
                $this->storageData->addAdditionalInputValue($relatedField, $id);
            }
        }

        $this->storeTranslations();
    }

    /**
     * Store the main forms' table row
     */
    private function storeMain()
    {
        $this->executeBeforeMainEvents();

        $table     = $this->storageData->getTable();
        $editId    = $this->storageData->getEditId();
        $mainInput = $this->storageData->getMainInput();

        $mainInput = $this->dbService->toStorageArray($mainInput);

        if ($editId) {
            if ($this->storageData->getFormInput() && $mainInput) {
                $this->dbService->update($table, $mainInput, ['id' => $editId]);
            }
        } else {
            $this->disableForeignKeysForTempKeys();
            $editId = $this->dbService->insert($table, $mainInput);
            $this->enableForeignKeysForTempKeys();
        }

        $this->storageData->setEditId($editId);

        $this->executeAfterMainEvents();
    }

    /**
     * Store data after the main forms' table row is inserted/updated
     */
    private function storePostMain()
    {
        $editId   = $this->storageData->getEditId();
        $langCode = $this->storageData->getLanguageCode();
        $model    = $this->storageData->getTable();

        $editData = $this->dbService->getTableRowById($model, $editId);

        /** @var Field $field */
        foreach ($this->storageData->getFieldMap() as $key => $field) {
            $value = $this->storageData->getFormInputValue($key);

            switch (true) {
                case $field->getStorage() instanceof OneToOne:
                    $this->fieldStorageService->storeOneToOne($field, $value, $editData, $langCode);
                break;

                case $field->getStorage() instanceof OneToMany:
                    $this->fieldStorageService->storeOneToMany($field, $editId, $editData);
                break;

                case $field->getStorage() instanceof ManyToMany:
                    $this->fieldStorageService->storeManyToMany($field, $value, $editId, $langCode);
                break;
            }
        }

        $this->executeAfterStoreEvents();
    }

    /**
     * Store translation fields
     */
    private function storeTranslations()
    {
        /** @var Field $field */
        foreach ($this->storageData->getFieldMap() as $key => $field) {
            $storage = $field->getStorage();

            if ( ! $storage instanceof Translation) {
                continue;
            }

            $value    = $this->storageData->getFormInputValue($key);
            $keyId    = $this->fieldStorageService->getTranslationKeyId($field, $this->storageData->getEditId());
            $langCode = $storage->getLanguageCode() ?: $this->storageData->getLanguageCode();

            $this->translationService->saveValue($value, $keyId, $langCode);
            $this->storageData->addAdditionalInputValue($field->getColumn(), $keyId);
        }
    }
}