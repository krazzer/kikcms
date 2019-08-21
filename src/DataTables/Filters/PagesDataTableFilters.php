<?php declare(strict_types=1);

namespace KikCMS\DataTables\Filters;


use KikCMS\Classes\DataTable\DataTableFilters;
use KikCMS\Models\Page;

class PagesDataTableFilters extends DataTableFilters
{
    /** @var string */
    private $pageType = Page::TYPE_PAGE;

    /**
     * @return string
     */
    public function getPageType(): string
    {
        return $this->pageType;
    }

    /**
     * @param string $pageType
     * @return $this|PagesDataTableFilters
     */
    public function setPageType(string $pageType): PagesDataTableFilters
    {
        $this->pageType = $pageType;
        return $this;
    }
}