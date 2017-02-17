<?php

namespace KikCMS\Classes\DataTable;


class Filters
{
    const FILTER_SEARCH         = 'search';
    const FILTER_PAGE           = 'page';
    const FILTER_SORT_COLUMN    = 'sortColumn';
    const FILTER_SORT_DIRECTION = 'sortDirection';
    const FILTER_PARENT_EDIT_ID = 'parentEditId';

    const FILTER_TYPES = [
        self::FILTER_SEARCH,
        self::FILTER_PAGE,
        self::FILTER_SORT_COLUMN,
        self::FILTER_SORT_DIRECTION,
        self::FILTER_PARENT_EDIT_ID,
    ];

    /** @var int */
    private $page = 1;

    /** @var string */
    private $search = '';

    /** @var string */
    private $sortColumn = '';

    /** @var string */
    private $sortDirection = 'asc';

    /** @var int */
    private $parentEditId = 0;

    /**
     * @param array $filters
     */
    public function setByArray(array $filters)
    {
        foreach (self::FILTER_TYPES as $filterType) {
            if (array_key_exists($filterType, $filters)) {
                $setMethod = 'set' . $filterType;
                $this->$setMethod($filters[$filterType]);
            }
        }
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
     * @return Filters
     */
    public function setPage(int $page): Filters
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
     * @return Filters
     */
    public function setSearch(string $search): Filters
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
     * @return Filters
     */
    public function setSortColumn(string $sortColumn): Filters
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
     * @return Filters
     */
    public function setSortDirection(string $sortDirection): Filters
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
     * @return Filters
     */
    public function setParentEditId(int $parentEditId): Filters
    {
        $this->parentEditId = $parentEditId;
        return $this;
    }
}