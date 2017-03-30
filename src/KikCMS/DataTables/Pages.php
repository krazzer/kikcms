<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Forms\LinkForm;
use KikCMS\Forms\MenuForm;
use KikCMS\Forms\PageForm;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\DataTable\PagesDataTableFilters;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property PageRearrangeService $pageRearrangeService
 */
class Pages extends DataTable
{
    /** @inheritdoc */
    protected $jsClass = 'PagesDataTable';

    /** @inheritdoc */
    protected $searchableFields = ['name'];

    /** @inheritdoc */
    protected $orderableFields = ['id' => 'p.id'];

    /** @inheritdoc */
    protected $preLoadWysiwygJs = true;

    /** @inheritdoc */
    protected $multiLingual = true;

    /** @inheritdoc */
    public $indexView = 'datatables/page/index';

    /** @inheritdoc */
    public $tableView = 'datatables/page/table';

    protected function addAssets()
    {
        parent::addAssets();

        $this->view->assets->addCss('cmsassets/css/pagesDataTable.css');
        $this->view->assets->addJs('cmsassets/js/pagesDataTable.js');
        $this->view->assets->addJs('cmsassets/js/datatable/sortControl.js');
        $this->view->assets->addJs('cmsassets/js/treeSortControl.js');
    }

    /**
     * @inheritdoc
     */
    public function delete(array $ids)
    {
        $deletedPages = Page::getByIdList($ids);

        parent::delete($ids);

        foreach ($deletedPages as $page) {
            $this->pageRearrangeService->updateLeftSiblingsOrder($page);
        }

        $this->pageRearrangeService->updateNestedSet();
    }

    /**
     * @inheritdoc
     */
    public function getEmptyFilters(): Filters
    {
        return new PagesDataTableFilters();
    }

    /**
     * @return PagesDataTableFilters|Filters
     */
    public function getFilters(): Filters
    {
        return parent::getFilters();
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): string
    {
        switch ($this->getFilters()->getPageType()) {
            case Page::TYPE_MENU:
                return 'dataTables.menus';
            break;

            case Page::TYPE_LINK:
                return 'dataTables.links';
            break;

            case Page::TYPE_ALIAS:
                return 'dataTables.aliases';
            break;
        }

        return 'dataTables.pages';
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Page::class;
    }

    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        switch ($this->getFilters()->getPageType()) {
            case Page::TYPE_MENU:
                return MenuForm::class;
            break;
            case Page::TYPE_LINK:
                return LinkForm::class;
            break;
        }

        return PageForm::class;
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultQuery()
    {
        $langCode        = $this->getFilters()->getLanguageCode();
        $defaultLangCode = $this->languageService->getDefaultLanguageCode();

        $query = new Builder();
        $query
            ->from(['p' => $this->getModel()])
            ->leftJoin(PageLanguage::class, 'p.id = pl.page_id AND pl.language_code = "' . $langCode . '"', 'pl')
            ->leftJoin(PageLanguage::class, 'p.id = pld.page_id AND pld.language_code = "' . $defaultLangCode . '"', 'pld')
            ->orderBy('IFNULL(p.lft, 99999 + IFNULL(p.display_order, 99999 + p.id)) asc')
            ->groupBy('p.id')
            ->columns([
                'pl.name', 'default_language_name' => 'pld.name', 'p.id', 'p.display_order', 'p.level', 'p.lft',
                'p.rgt', 'p.type', 'p.parent_id', 'p.menu_max_level'
            ]);

        return $query;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->setFieldFormatting('name', [$this, 'formatName']);
    }

    /**
     * @param $value
     * @param array $rowData
     * @return string
     */
    protected function formatName($value, array $rowData)
    {
        // disable dragging / tree structure when sorting or searching
        if ($this->filters->getSearch() || $this->filters->getSortColumn()) {
            return $value;
        }

        if ($rowData[Page::FIELD_TYPE] == Page::TYPE_LINK) {
            $value = '<span class="glyphicon glyphicon-link"></span> ' . $value;
        }

        return '<span class="name">' . $value . '</span>';
    }
}