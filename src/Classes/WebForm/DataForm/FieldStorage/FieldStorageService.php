<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;


use Exception;
use InvalidArgumentException;
use KikCMS\Classes\DataTable\DataTable;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Services\TranslationService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Handles the storage of a single field
 *
 * @property DbService $dbService
 * @property TranslationService $translationService
 * @property StorageService $storageService
 */
class FieldStorageService extends Injectable
{
    /** @var array */
    private $oneToOneRefCache = [];

    /** @var array */
    private $translationKeyCache = [];

    /**
     * @param Field $field
     * @param int $relationId
     * @return int
     * @deprecated Use RelationKeys instead
     */
    public function getTranslationKeyId(Field $field, int $relationId = null): int
    {
        if ( ! $relationId) {
            if (array_key_exists($field->getColumn(), $this->translationKeyCache)) {
                return $this->translationKeyCache[$field->getColumn()];
            }

            $translationKeyId = $this->translationService->createNewTranslationKeyId();

            $this->translationKeyCache[$field->getKey()] = $translationKeyId;

            return $translationKeyId;
        }

        $query = (new Builder())
            ->from($field->getStorage()->getTableModel())
            ->columns($field->getColumn())
            ->where(DataTable::TABLE_KEY . ' = :id:', ['id' => $relationId]);

        if ($translationKeyId = $this->dbService->getValue($query)) {
            return $translationKeyId;
        }

        return $this->translationService->createNewTranslationKeyId();
    }

    /**
     * @param Field $field
     * @param $value
     * @param $editId
     * @param array $editData
     * @param string|null $langCode
     * @return bool
     * @throws Exception
     * @deprecated Use RelationKeys instead
     */
    public function store(Field $field, $value, $editId, array $editData, string $langCode = null): bool
    {
        $storage = $field->getStorage();

        switch (true) {
            case $storage instanceof OneToOne:
                return $this->storeOneToOne($field, $value, $editData, $langCode);
            break;

            case $storage instanceof OneToMany:
                return $this->storeOneToMany($field, $editId, $editData);
            break;

            case $storage instanceof ManyToMany:
                return $this->storeManyToMany($field, $value, $editId, $langCode);
            break;

            default:
                throw new Exception('Not implemented yet');
            break;
        }
    }

    /**
     * @param Field $field
     * @param $value
     * @param array $editData
     * @param string|null $langCode
     * @return bool|int
     * @deprecated Use RelationKeys instead
     */
    public function storeOneToOne(Field $field, $value, array $editData, string $langCode = null)
    {
        /** @var OneToOne $storage */
        $storage = $field->getStorage();
        $value   = $this->dbService->toStorage($value);
        $editId  = $editData[$storage->getRelatedByField()];

        $set   = [$field->getColumn() => $value];
        $where = $storage->getDefaultValues() + [$storage->getRelatedField() => $editId];

        if ($storage->isAddLanguageCode()) {
            $where[$storage->getLanguageCodeField()] = $langCode;
        }

        if ($this->relationRowExists($field, $editId, $langCode)) {
            if( ! $value && $storage->isRemoveOnEmpty()){
                return $this->dbService->delete($storage->getTableModel(), $where);
            }

            return $this->dbService->update($storage->getTableModel(), $set, $where);
        }

        if ( ! $value) {
            return false;
        }

        return $this->dbService->insert($storage->getTableModel(), $set + $where);
    }

    /**
     * @param Field|DataTableField $field
     * @param $editId
     * @param array $editData
     * @return bool
     */
    public function storeOneToMany(Field $field, $editId, array $editData): bool
    {
        $dataTable = $field->getDataTable();

        $keysToUpdate = $dataTable->getCachedNewIds();
        $relatedField = $dataTable->getParentRelationKey();
        $model        = $dataTable->getModel();

        $relatedValue = $this->storageService->getRelatedValueForField($field, $editData, $editId);

        foreach ($keysToUpdate as $newId) {
            $success = $this->dbService->update($model, [$relatedField => $relatedValue], [DataTable::TABLE_KEY => $newId, $relatedField => 0]);

            if ( ! $success) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Field $field
     * @param $value
     * @param $editId
     * @param string|null $langCode
     *
     * @return bool
     * @deprecated Use RelationKeys instead
     */
    public function storeManyToMany(Field $field, $value, $editId, string $langCode = null): bool
    {
        if ( ! $value) {
            $value = [];
        }

        if ( ! is_array($value)) {
            throw new InvalidArgumentException(static::class . ' can only store array values');
        }

        /** @var ManyToMany $storage */
        $storage  = $field->getStorage();
        $fieldKey = $field->getKey();

        $table        = $storage->getTableModel();
        $relatedField = $storage->getRelatedField();

        $where = [$relatedField => $editId] + $storage->getDefaultValues();

        if ($storage->isAddLanguageCode()) {
            $where[$storage->getLanguageCodeField()] = $langCode;
        }

        $this->dbService->delete($table, $where);

        foreach ($value as $key => $id) {
            $insert            = $where;
            $insert[$fieldKey] = $id;

            // don't story empty values
            if ( ! $id) {
                continue;
            }

            if ($keyField = $storage->getKeyField()) {
                $insert[$keyField] = $key;
            }

            $this->dbService->insert($table, $insert);
        }

        return true;
    }

    /**
     * @param Field $field
     * @param $id
     * @param string|null $langCode
     *
     * @return array
     * @deprecated Use RelationKeys instead
     */
    public function retrieveManyToMany(Field $field, $id, string $langCode = null)
    {
        /** @var ManyToMany $storage */
        $storage = $field->getStorage();

        $query = (new Builder())
            ->columns($field->getKey())
            ->addFrom($storage->getTableModel())
            ->andWhere($storage->getRelatedField() . ' = ' . $id);

        if ($storage->isAddLanguageCode()) {
            $query->andWhere($storage->getLanguageCodeField() . ' = :langCode:', [
                'langCode' => $langCode
            ]);
        }

        if ($keyField = $storage->getKeyField()) {
            $query->columns([$keyField, $field->getKey()]);
            return $this->dbService->getAssoc($query);
        }

        $query->columns($field->getKey());
        return $this->dbService->getValues($query);
    }

    /**
     * @param Field $field
     * @param array $tableData
     * @param string|null $langCode
     * @return mixed
     * @deprecated Use RelationKeys instead
     */
    public function retrieveOneToOne(Field $field, array $tableData, string $langCode = null)
    {
        $id = $tableData[$field->getStorage()->getRelatedByField()];

        $query = $this->getRelationQuery($field, $id, $langCode)->columns($field->getColumn());
        return $this->dbService->getValue($query);
    }

    /**
     * @param Field $field
     * @param array $tableData
     *
     * @return mixed
     * @deprecated Use RelationKeys instead
     */
    public function retrieveOneToOneRef(Field $field, array $tableData)
    {
        $storage = $field->getStorage();

        if ( ! isset($tableData[$storage->getRelatedField()])) {
            return null;
        }

        $referenceId = $tableData[$storage->getRelatedField()];
        $cacheKey    = $storage->getTableModel() . $referenceId;

        if ( ! array_key_exists($cacheKey, $this->oneToOneRefCache)) {
            $this->oneToOneRefCache[$cacheKey] = $this->getReferencedTableData($storage, $referenceId);
        }

        return $this->oneToOneRefCache[$cacheKey][$field->getColumn()];
    }

    /**
     * @param Field $field
     * @param $id
     * @param string|null $langCode
     *
     * @return null|string
     * @deprecated Use RelationKeys instead
     */
    public function retrieveTranslation(Field $field, $id, string $langCode = null): ?string
    {
        $langCode         = $field->getStorage()->getLanguageCode() ?: $langCode;
        $translationKeyId = $this->getTranslationKeyId($field, $id);

        return $this->translationService->getTranslationValue($translationKeyId, $langCode);
    }

    /**
     * @param FieldStorage $storage
     * @param int $referenceId
     *
     * @return array
     */
    private function getReferencedTableData(FieldStorage $storage, int $referenceId): array
    {
        $query = (new Builder())
            ->from($storage->getTableModel())
            ->where(DataTable::TABLE_KEY . ' = :id:', ['id' => $referenceId]);

        return $this->dbService->getRow($query);
    }

    /**
     * @param Field $field
     * @param $relationId
     * @param string $langCode
     *
     * @return Builder
     * @deprecated Use RelationKeys instead
     */
    private function getRelationQuery(Field $field, $relationId, string $langCode = null)
    {
        /** @var FieldStorage $storage */
        $storage = $field->getStorage();

        $relationQuery = (new Builder())
            ->addFrom($storage->getTableModel())
            ->where($storage->getRelatedField() . ' = ' . $relationId);

        foreach ($storage->getDefaultValues() as $field => $value) {
            $relationQuery->andWhere($field . ' = ' . $this->db->escapeString($value));
        }

        if ($storage->isAddLanguageCode()) {
            $relationQuery->andWhere($storage->getLanguageCodeField() . ' = ' . $this->db->escapeString($langCode));
        }

        return $relationQuery;
    }

    /**
     * @param Field $field
     * @param $relationId
     * @param string $langCode
     *
     * @return bool
     * @deprecated Use RelationKeys instead
     */
    private function relationRowExists(Field $field, $relationId, $langCode = null): bool
    {
        return $this->dbService->getExists($this->getRelationQuery($field, $relationId, $langCode));
    }
}