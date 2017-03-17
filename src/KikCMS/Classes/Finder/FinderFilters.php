<?php

namespace KikCMS\Classes\Finder;


use KikCMS\Classes\Renderable\Filters;

class FinderFilters extends Filters
{
    /** @var int */
    private $folderId = 0;

    /** @var string */
    private $search = '';

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * @param string $search
     * @return FinderFilters
     */
    public function setSearch(string $search): FinderFilters
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return int
     */
    public function getFolderId(): int
    {
        return $this->folderId;
    }

    /**
     * @param int $folderId
     * @return FinderFilters
     */
    public function setFolderId(int $folderId): FinderFilters
    {
        $this->folderId = $folderId;
        return $this;
    }
}