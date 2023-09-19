<?php declare(strict_types=1);

namespace KikCMS\Services\WebForm;

use Exception;
use InvalidArgumentException;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Exceptions\DuplicateTemporaryDataTableKeyException;
use KikCMS\Classes\WebForm\DataForm\StorageData;
use KikCMS\Services\ModelService;
use KikCmsCore\Config\DbConfig;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\WebForm\DataForm\Events\StoreEvent;
use KikCMS\Services\TranslationService;
use Monolog\Logger;
use KikCMS\Classes\Phalcon\Injectable;
use Psr\Log\LogLevel;

/**
 * Service for handling a DataForms' Storage, using the StorageData object
 *
 * @property DbService $dbService
 * @property TranslationService $translationService
 * @property RelationKeyService $relationKeyService
 * @property ModelService $modelService
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
            $this->executeBeforeStoreEvents();
            $this->storeMain();
            $this->executeAfterStoreEvents();
        } catch (Exception $exception) {
            $this->db->rollback();

            if($this->isDuplicateTempKeyError($exception)){
                throw new DuplicateTemporaryDataTableKeyException;
            }

            $this->logger->log(Logger::ERROR, $exception);
            return false;
        }

        if ($success = $this->db->commit()) {
            foreach ($this->storageData->getDataTableFieldMap() as $field) {
                $field->getDataTable()->removeNewIdCache();
            }
        }

        return $success;
    }

    /**
     * If a temporary key is inserted, fk checks needs to be disabled for insert
     */
    private function disableForeignKeysForTempKeys(): void
    {
        if ($this->storageData->hasTempParentEditId()) {
            $this->dbService->setForeignKeyChecks(false);
        }
    }

    /**
     * If a temporary key is inserted, fk checks needs to be re-enabled after insert
     */
    private function enableForeignKeysForTempKeys(): void
    {
        if ($this->storageData->hasTempParentEditId()) {
            $this->dbService->setForeignKeyChecks(true);
        }
    }

    /**
     * @param string $eventType
     */
    private function executeEvents(string $eventType): void
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
    private function executeAfterMainEvents(): void
    {
        $this->executeEvents(StoreEvent::AFTER_MAIN_STORE);
    }

    /**
     * Execute after the main insert/update is completed
     */
    private function executeAfterStoreEvents(): void
    {
        $this->executeEvents(StoreEvent::AFTER_STORE);
    }

    /**
     * Execute after the main insert/update is completed
     */
    private function executeBeforeStoreEvents(): void
    {
        $this->executeEvents(StoreEvent::BEFORE_STORE);
    }

    /**
     * Execute after the main insert/update is completed
     */
    private function executeBeforeMainEvents(): void
    {
        $this->executeEvents(StoreEvent::BEFORE_MAIN_STORE);
    }

    /**
     * Store the main forms' table row
     */
    private function storeMain(): void
    {
        $mainInput = $this->storageData->getMainInput();
        $langCode  = $this->storageData->getLanguageCode();
        $object    = $this->storageData->getObject();

        $preSaveRelations = [];

        // set objects' properties
        foreach ($mainInput as $key => $value) {
            if ($this->relationKeyService->isRelationKey($key)) {
                $localPreSaveRelations = $this->relationKeyService->set($object, $key, $value, $langCode);
                $preSaveRelations = array_merge($preSaveRelations, $localPreSaveRelations);
            } else {
                $object->$key = $this->dbService->toStorage($value);
            }
        }

        // set subdatatables
        $this->setSubDataTableData();
        $this->executeBeforeMainEvents();

        if (property_exists($object, DataTable::TABLE_KEY)) {
            $this->relationKeyService->savePreSaveRelations($object, $preSaveRelations);
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
    private function setSubDataTableData(): void
    {
        foreach ($this->storageData->getDataTableFieldMap() as $key => $field) {
            $keysToUpdate = $field->getDataTable()->getCachedNewIds();
            $relatedModel = $field->getDataTable()->getModel();

            $this->storageData->getObject()->$key = $this->modelService->getObjects($relatedModel, $keysToUpdate);
        }
    }

    /**
     * @param Exception $exception
     * @return bool
     */
    private function isDuplicateTempKeyError(Exception $exception): bool
    {
        $pattern = '/' . DbConfig::ERROR_CODE_DUPLICATE_ENTRY .'[a-zA-Z ]+\'([0-9\-+]+)\'/';

        if( ! preg_match($pattern, $exception->getMessage(), $matches)) {
            return false;
        }

        $keys = explode('-', $matches[1]);

        return in_array(0, $keys);
    }
}