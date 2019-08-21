<?php declare(strict_types=1);

namespace KikCMS\Classes\Finder;


use KikCMS\Classes\Renderable\Filters;

class FinderFilters extends Filters
{
    /** @var int|null */
    private $folderId;

    /** @var string|null */
    private $search;

    /**
     * @return string|null
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }

    /**
     * @param string|null $search
     * @return FinderFilters
     */
    public function setSearch(?string $search): FinderFilters
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getFolderId(): ?int
    {
        return $this->folderId;
    }

    /**
     * @param int|null $folderId
     * @return FinderFilters
     */
    public function setFolderId(?int $folderId): FinderFilters
    {
        $this->folderId = $folderId;
        return $this;
    }
}