<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Translator;
use KikCMS\Forms\LinkForm;
use KikCMS\Forms\MenuForm;
use KikCMS\Forms\PageForm;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Models\Template;
use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\DataTable\PagesDataTableFilters;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property PageRearrangeService $pageRearrangeService
 * @property Translator $translator
 */
class Pages extends DataTable
{
    /** @inheritdoc */
    protected $jsClass = 'PagesDataTable';

    /** @inheritdoc */
    protected $searchableFields = ['pl.name'];

    /** @inheritdoc */
    protected $orderableFields = ['id' => 'p.id', 'name' => 'pl.name'];

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
    public function getLabels(): array
    {
        $translation = 'pages';

        switch ($this->getFilters()->getPageType()) {
            case Page::TYPE_MENU:
                $translation = 'menus';
            break;
            case Page::TYPE_LINK:
                $translation = 'links';
            break;
            case Page::TYPE_ALIAS:
                $translation = 'aliases';
            break;
        }

        return [
            $this->translator->tlb('dataTables.' . $translation . '.singular'),
            $this->translator->tlb('dataTables.' . $translation . '.plural'),
        ];
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
     * @return array
     */
    protected function getAllowedTemplateIds(): array
    {
        $query = (new Builder())
            ->columns(['id'])
            ->from(Template::class)
            ->where('hide = 0');

        return $this->dbService->getValues($query);
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
            ->leftJoin(Template::class, 'p.template_id = t.id', 't')
            ->where('t.id IS NULL OR t.id IN ({templateIds:array})', ['templateIds' => $this->getAllowedTemplateIds()])
            ->orderBy('IFNULL(p.lft, 99999 + IFNULL(p.display_order, 99999 + p.id)) asc')
            ->groupBy('p.id')
            ->columns([
                'pld.name AS default_language_name', 't.name AS template', 'pl.name', 'p.id', 'p.display_order',
                'p.level', 'p.lft', 'p.rgt', 'p.type', 'p.parent_id', 'p.menu_max_level', 'pl.active', 'pl.url',
                'pl.id AS plid'
            ]);

        return $query;
    }

    /**
     * @inheritdoc
     */
    protected function getTableFieldMap(): array
    {
        return [
            'name'     => $this->translator->tlb('name'),
            'template' => $this->translator->tlb('template'),
            'url'      => $this->translator->tlb('url'),
            'id'       => $this->translator->tlb('id'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->setFieldFormatting('name', [$this, 'formatName']);
        $this->setFieldFormatting('type', [$this, 'formatType']);

        $this->addTableButton('eye-open', $this->translator->tlb('dataTables.pages.preview'), 'preview');
    }

    /**
     * @param $value
     * @param array $rowData
     * @return string
     */
    protected function formatName($value, array $rowData)
    {
        if ( ! $value && $rowData['default_language_name']) {
            $value = '<span class="defaultLanguagePlaceHolder">' . $rowData['default_language_name'] . '</span>';
        }

        // disable dragging / tree structure when sorting or searching
        if ($this->filters->getSearch() || $this->filters->getSortColumn()) {
            return $value;
        }

        $linkTitle     = $this->translator->tlb('dataTables.pages.titles.link');
        $inactiveTitle = $this->translator->tlb('dataTables.pages.titles.inactive');

        if ($rowData[Page::FIELD_TYPE] == Page::TYPE_LINK) {
            $value = '<span class="glyphicon glyphicon-link" title="' . $linkTitle . '"></span> ' . $value;
        }

        if ( ! $rowData[PageLanguage::FIELD_ACTIVE] && $rowData[Page::FIELD_TYPE] == Page::TYPE_PAGE) {
            $value = '<span class="glyphicon glyphicon-eye-close" title="' . $inactiveTitle . '"></span> ' . $value;
        }

        return '<span class="name">' . $value . '</span>';
    }

    /**
     * @param $value
     * @return string
     */
    protected function formatType($value)
    {
        return $this->translator->tlb('dataTables.pages.' . $value);
    }
}