<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;

use Exception;
use InvalidArgumentException;
use KikCMS\Classes\DbService;
use KikCMS\Classes\WebForm\DataForm\Events\StoreEvent;
use KikCMS\Classes\WebForm\Field;
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
     * @return mixed
     * @throws Exception
     */
    public function retrieve(Field $field, $id, string $langCode = null)
    {
        switch (true) {
            case $field->getStorage() instanceof OneToOne:
                return $this->fieldStorageService->retrieveOneToOne($field, $id, $langCode);
            break;

            case $field->getStorage() instanceof OneToOneRef:
                return $this->fieldStorageService->retrieveOneToOneRef($field, $id, $langCode);
            break;

            case $field->getStorage() instanceof ManyToMany:
                return $this->fieldStorageService->retrieveManyToMany($field, $id, $langCode);
            break;

            case $field->getStorage() instanceof Translation:
                return $this->fieldStorageService->retrieveTranslation($field, $id, $langCode);
            break;

            case $field->getStorage() instanceof OneToMany:
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
        if( ! array_key_exists($eventType, $this->storageData->getEvents())){
            return;
        }

        $events = $this->storageData->getEvents()[$eventType];

        foreach ($events as $event){
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
    private function getRelatedFieldValue(FieldStorage $fieldStorage)
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

            if( ! $value = $this->storageData->getValue($key)){
                continue;
            }

            if ( ! array_key_exists($relatedField, $fields)) {
                $fields[$relatedField] = (new StorageValues())->setFieldStorage($storage);
            }

            $fields[$relatedField]->add($field->getTableField(), $value);
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
                $this->storageData->addValue($relatedField, $id);
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
            if ($this->storageData->getInput()) {
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

        /** @var Field $field */
        foreach ($this->storageData->getFieldMap() as $key => $field) {
            $value = $this->storageData->getValue($key);

            switch (true) {
                case $field->getStorage() instanceof OneToOne:
                    $this->fieldStorageService->storeOneToOne($field, $value, $editId, $langCode);
                break;

                case $field->getStorage() instanceof OneToMany:
                    $this->fieldStorageService->storeOneToMany($field, $editId);
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

            $value    = $this->storageData->getValue($key);
            $keyId    = $this->fieldStorageService->getTranslationKeyId($field, $this->storageData->getEditId());
            $langCode = $storage->getLanguageCode() ?: $this->storageData->getLanguageCode();

            $this->translationService->saveValue($value, $keyId, $langCode);
            $this->storageData->setValue($key, $keyId);
        }
    }
}