<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;

/**
 * Contains the FieldStorage object, and a valueMap [key => value] to store
 */
class StorageValues
{
    /** @var FieldStorage */
    private $fieldStorage;

    /** @var array */
    private $valueMap;

    /**
     * @param string $key
     * @param $value
     * @return $this|StorageValues
     */
    public function add(string $key, $value): StorageValues
    {
        $this->valueMap[$key] = $value;
        return $this;
    }

    /**
     * @return FieldStorage
     */
    public function getFieldStorage(): FieldStorage
    {
        return $this->fieldStorage;
    }

    /**
     * @param FieldStorage $fieldStorage
     * @return StorageValues
     */
    public function setFieldStorage(FieldStorage $fieldStorage): StorageValues
    {
        $this->fieldStorage = $fieldStorage;
        return $this;
    }

    /**
     * @return array
     */
    public function getValueMap(): array
    {
        return $this->valueMap;
    }

    /**
     * @param array $valueMap
     * @return StorageValues
     */
    public function setValueMap(array $valueMap): StorageValues
    {
        $this->valueMap = $valueMap;
        return $this;
    }
}