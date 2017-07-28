<?php

namespace KikCMS\Classes\DataTable;


use KikCMS\Classes\Renderable\Filters;

class DataTableFilters extends Filters
{
    /** @var int */
    private $page = 1;

    /** @var string */
    private $search = '';

    /** @var string */
    private $sortColumn = '';

    /** @var string */
    private $sortDirection = 'asc';

    /** @var int */
    private $editId = null;

    /** @var int */
    private $parentEditId = 0;

    /** @var null|string */
    private $languageCode = null;

    /** @var null|string */
    private $windowLanguageCode = null;

    /** @var array */
    private $customFilterValues = [];

    /**
     * @return null|int
     */
    public function getEditId(): ?int
    {
        return $this->editId;
    }

    /**
     * @param int $editId
     * @return DataTableFilters
     */
    public function setEditId(int $editId): DataTableFilters
    {
        $this->editId = $editId;
        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return DataTableFilters
     */
    public function setPage(int $page): DataTableFilters
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * @param string $search
     * @return DataTableFilters
     */
    public function setSearch(string $search): DataTableFilters
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return string
     */
    public function getSortColumn(): string
    {
        return $this->sortColumn;
    }

    /**
     * @param string $sortColumn
     * @return DataTableFilters
     */
    public function setSortColumn(string $sortColumn): DataTableFilters
    {
        $this->sortColumn = $sortColumn;
        return $this;
    }

    /**
     * @return string
     */
    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    /**
     * @param string $sortDirection
     * @return DataTableFilters
     */
    public function setSortDirection(string $sortDirection): DataTableFilters
    {
        $this->sortDirection = $sortDirection;
        return $this;
    }

    /**
     * @return int
     */
    public function getParentEditId(): int
    {
        return $this->parentEditId;
    }

    /**
     * @param int $parentEditId
     * @return DataTableFilters
     */
    public function setParentEditId(int $parentEditId): DataTableFilters
    {
        $this->parentEditId = $parentEditId;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    /**
     * @param null|string $languageCode
     * @return DataTableFilters
     */
    public function setLanguageCode(?string $languageCode): DataTableFilters
    {
        $this->languageCode = $languageCode;
        return $this;
    }

    /**
     * @return array
     */
    public function getCustomFilterValues(): array
    {
        return $this->customFilterValues;
    }

    /**
     * @param array $customFilterValues
     * @return DataTableFilters
     */
    public function setCustomFilterValues(array $customFilterValues): DataTableFilters
    {
        $this->customFilterValues = $customFilterValues;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getWindowLanguageCode(): ?string
    {
        return $this->windowLanguageCode ?: $this->getLanguageCode();
    }

    /**
     * @param null|string $windowLanguageCode
     * @return DataTableFilters
     */
    public function setWindowLanguageCode($windowLanguageCode): DataTableFilters
    {
        $this->windowLanguageCode = $windowLanguageCode;
        return $this;
    }
}