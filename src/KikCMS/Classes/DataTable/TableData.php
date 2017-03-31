<?php

namespace KikCMS\Classes\DataTable;

/**
 * Value object containing the data to use in a DataTable's table
 */
class TableData
{
    /** @var array */
    private $data = [];

    /** @var array contains the fields that are to be shown */
    private $displayMap = [];

    /** @var array contains the pagination pages */
    private $pages = [];

    /** @var int */
    private $totalItems;

    /** @var int */
    private $limit;

    /** @var int */
    private $current;

    /** @var int */
    private $totalPages;

    /**
     * @return array
     */
    public function getTableHeadColumns(): array
    {
        if ($this->displayMap) {
            return $this->displayMap;
        }

        if ( ! $this->data) {
            return [];
        }

        return array_combine(array_keys($this->data[0]), array_keys($this->data[0]));
    }

    /**
     * Create an array with only the values to display in a row
     *
     * @param int $index
     * @return array
     */
    public function getRowDisplayValues(int $index): array
    {
        $allRowData = $this->data[$index];

        $rowDataToDisplay = [];

        if ( ! $this->displayMap) {
            return $allRowData;
        }

        foreach ($this->displayMap as $fieldKey => $fieldName) {
            $rowDataToDisplay[$fieldKey] = $allRowData[$fieldKey];
        }

        return $rowDataToDisplay;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return TableData
     */
    public function setData(array $data): TableData
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getDisplayMap(): array
    {
        return $this->displayMap;
    }

    /**
     * @param array $displayMap
     * @return TableData
     */
    public function setDisplayMap(array $displayMap): TableData
    {
        $this->displayMap = $displayMap;
        return $this;
    }

    /**
     * @return array
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * @param array $pages
     * @return TableData
     */
    public function setPages(array $pages): TableData
    {
        $this->pages = $pages;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * @param int $totalItems
     * @return TableData
     */
    public function setTotalItems(int $totalItems): TableData
    {
        $this->totalItems = $totalItems;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return TableData
     */
    public function setLimit(int $limit): TableData
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrent(): int
    {
        return $this->current;
    }

    /**
     * @param int $current
     * @return TableData
     */
    public function setCurrent(int $current): TableData
    {
        $this->current = $current;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @param int $totalPages
     * @return TableData
     */
    public function setTotalPages(int $totalPages): TableData
    {
        $this->totalPages = $totalPages;
        return $this;
    }
}