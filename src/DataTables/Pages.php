<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Frontend\Extendables\TemplateFieldsBase;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Translator;
use KikCMS\Forms\LinkForm;
use KikCMS\Forms\MenuForm;
use KikCMS\Forms\PageForm;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\CacheService;
use KikCMS\Services\Cms\UserSettingsService;
use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\DataTable\PagesDataTableFilters;
use KikCMS\Services\DataTable\PagesDataTableService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\TemplateService;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property PageRearrangeService $pageRearrangeService
 * @property Translator $translator
 * @property AccessControl $acl
 * @property TemplateService $templateService
 * @property TemplateFieldsBase $templateFields
 * @property PagesDataTableService $pagesDataTableService
 * @property UserSettingsService $userSettingsService
 * @property PageService $pageService
 * @property CacheService $cacheService
 * @property WebsiteSettingsBase $websiteSettings
 */
class Pages extends DataTable
{
    /** @inheritdoc */
    protected $jsClass = 'PagesDataTable';

    /** @inheritdoc */
    protected $searchableFields = ['pl.name'];

    /** @inheritdoc */
    protected $preLoadWysiwygJs = true;

    /** @inheritdoc */
    protected $multiLingual = true;

    /** @inheritdoc */
    public $indexView = 'datatables/page/index';

    /** @inheritdoc */
    public $tableView = 'datatables/page/table';

    /** @var string */
    private $linkTitle;

    /** @var string */
    private $inactiveTitle;

    /** @var string */
    private $lockedTitle;

    /** @var array */
    private $closedPageIdMapCache = [];

    /**
     * @inheritdoc
     */
    public function delete(array $ids)
    {
        foreach ($ids as $pageId) {
            $page = Page::getById($pageId);

            if ( ! $page || $page->key) {
                continue;
            }

            if ( ! $this->acl->canDeleteMenu() && $page->type == Page::TYPE_MENU) {
                continue;
            }

            parent::delete([$pageId]);

            $this->pageRearrangeService->updateLeftSiblingsOrder($page->getParentId(), $page->getDisplayOrder());
        }

        $this->pageRearrangeService->updateNestedSet();
        $this->cacheService->clearMenuCache();
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
            $this->translator->tl('dataTables.' . $translation . '.singular'),
            $this->translator->tl('dataTables.' . $translation . '.plural'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return $this->websiteSettings->getPageClass();
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

        if ($template = $this->pagesDataTableService->getTemplate($this)) {
            if ($formClass = $template->getForm()) {
                return $formClass;
            }
        }

        return PageForm::class;
    }

    /**
     * @param int $pageId
     * @return bool
     */
    public function isHidden(int $pageId): bool
    {
        if ($this->filters->getSearch() || $this->filters->getSortColumn()) {
            return false;
        }

        $closedPageIdMap = $this->getClosedPageIdMap();

        foreach ($closedPageIdMap as $closedPageIds) {
            if (in_array($pageId, $closedPageIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getAllowedTemplateKeys(): array
    {
        return $this->templateService->getAllowedKeys();
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultQuery()
    {
        $langCode        = $this->getFilters()->getLanguageCode();
        $defaultLangCode = $this->languageService->getDefaultLanguageCode();

        $query = (new Builder)
            ->from(['p' => $this->getModel()])
            ->leftJoin(PageLanguage::class, 'IF(p.type = "alias", p.alias, p.id) = pl.page_id AND pl.language_code = "' . $langCode . '"', 'pl')
            ->leftJoin(PageLanguage::class, 'IF(p.type = "alias", p.alias, p.id) = pld.page_id AND pld.language_code = "' . $defaultLangCode . '"', 'pld')
            ->leftJoin(Page::class, 'p.alias = pali.id', 'pali')
            ->orderBy('IFNULL(p.lft, 99999 + IFNULL(p.display_order, 99999 + p.id)) asc')
            ->groupBy('p.id')
            ->columns([
                'pld.name AS default_language_name', 'p.template', 'pl.name', 'p.id', 'p.display_order',
                'p.level', 'p.lft', 'p.rgt', 'p.type', 'p.parent_id', 'p.menu_max_level', 'pl.active', 'pl.url',
                'pl.id AS plid', 'p.key'
            ]);

        if ( ! $this->getAllowedTemplateKeys()) {
            return $query;
        }

        return $query->where('IFNULL(pali.template, p.template) IS NULL OR IFNULL(pali.template, p.template) IN ({templateKeys:array})', [
            'templateKeys' => $this->getAllowedTemplateKeys()
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getTableFieldMap(): array
    {
        return [
            'name'     => $this->translator->tl('fields.name'),
            'template' => $this->translator->tl('fields.template'),
            'url'      => $this->translator->tl('fields.url'),
            'id'       => $this->translator->tl('fields.id'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->linkTitle     = $this->translator->tl('dataTables.pages.titles.link');
        $this->inactiveTitle = $this->translator->tl('dataTables.pages.titles.inactive');
        $this->lockedTitle   = $this->translator->tl('dataTables.pages.titles.locked');

        $this->setFieldFormatting('name', [$this, 'formatName']);
        $this->setFieldFormatting('template', [$this, 'formatTemplateName']);

        $this->addTableButton('eye-open', $this->translator->tl('dataTables.pages.preview'), 'preview');
    }

    /**
     * @param $templateKey
     * @return string
     */
    protected function formatTemplateName($templateKey): string
    {
        $templates = $this->templateFields->getTemplates();

        foreach ($templates as $template) {
            if ($template->getKey() == $templateKey) {
                return $template->getName();
            }
        }

        return (string) $templateKey;
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

        if ($rowData[Page::FIELD_TYPE] == Page::TYPE_MENU) {
            $value = $rowData['default_language_name'];
        }

        // disable dragging / tree structure when sorting or searching
        if ($this->filters->getSearch() || $this->filters->getSortColumn()) {
            return $value;
        }

        if ($rowData[Page::FIELD_TYPE] == Page::TYPE_LINK) {
            $value = '<span class="glyphicon glyphicon-link" title="' . $this->linkTitle . '"></span> ' . $value;
        }

        if ($rowData[Page::FIELD_KEY] && $rowData[Page::FIELD_TYPE] != Page::TYPE_MENU) {
            $value = '<span class="glyphicon glyphicon-lock" title="' . $this->lockedTitle . '"></span> ' . $value;
        }

        if ($rowData[Page::FIELD_TYPE] == Page::TYPE_ALIAS) {
            $value = '<span class="glyphicon glyphicon-share-alt" title="' . $this->linkTitle . '"></span> ' . $value;
        }

        if ( ! $rowData[PageLanguage::FIELD_ACTIVE] && $rowData[Page::FIELD_TYPE] == Page::TYPE_PAGE) {
            $value = '<span class="glyphicon glyphicon-eye-close" title="' . $this->inactiveTitle . '"></span> ' . $value;
        }

        $closedPageIdMap = $this->getClosedPageIdMap();

        $arrowClass = array_key_exists($rowData[Page::FIELD_ID], $closedPageIdMap) ? ' closed' : '';

        return '<span class="arrow' . $arrowClass . '"></span><span class="name">' . $value . '</span>';
    }

    /**
     * Returns an array with pageIds that are collapsed
     *
     * @return array [closedPageId => [childIds]
     */
    private function getClosedPageIdMap(): array
    {
        if ($this->closedPageIdMapCache) {
            return $this->closedPageIdMapCache;
        }

        $closedPageIds = $this->userSettingsService->getClosedPageIdsByClass(static::class);

        $this->closedPageIdMapCache = $this->pageService->getOffspringIdMap($closedPageIds);

        return $this->closedPageIdMapCache;
    }
}