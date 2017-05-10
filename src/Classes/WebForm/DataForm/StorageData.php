<?php

namespace KikCMS\Classes\WebForm\DataForm;

/**
 * Contains formatted data for storage
 */
class StorageData
{
    /** @var array data that will be stored in the DataForm's table */
    private $dataStoredInTable = [];

    /** @var array data stored elsewhere */
    private $dataStoredElseWhere = [];

    /**
     * @return array
     */
    public function getDataStoredInTable(): array
    {
        return $this->dataStoredInTable;
    }

    /**
     * @param array $dataStoredInTable
     * @return StorageData
     */
    public function setDataStoredInTable(array $dataStoredInTable): StorageData
    {
        $this->dataStoredInTable = $dataStoredInTable;
        return $this;
    }

    /**
     * @return array
     */
    public function getDataStoredElseWhere(): array
    {
        return $this->dataStoredElseWhere;
    }

    /**
     * @param array $dataStoredElseWhere
     * @return StorageData
     */
    public function setDataStoredElseWhere(array $dataStoredElseWhere): StorageData
    {
        $this->dataStoredElseWhere = $dataStoredElseWhere;
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @param bool $isStoredElsewhere
     */
    public function addValue(string $key, $value, bool $isStoredElsewhere = false)
    {
        if ($isStoredElsewhere) {
            $this->dataStoredElseWhere[$key] = $value;
        } else {
            $this->dataStoredInTable[$key] = $value;
        }
    }
}