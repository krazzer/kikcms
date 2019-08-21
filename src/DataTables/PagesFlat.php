<?php declare(strict_types=1);


namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Forms\PageForm;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\DataTables\Filters\PagesDataTableFilters;

/**
 * Pages datatable for one-level, one-template
 */
abstract class PagesFlat extends DataTable
{
    protected $jsClass = 'PagesFlatDataTable';

    /**
     * @return string
     */
    abstract function getTemplate(): string;

    /**
     * @inheritdoc
     */
    public function getDefaultQuery()
    {
        $langCode = $this->getFilters()->getLanguageCode();

        return parent::getDefaultQuery()
            ->leftJoin(PageLanguage::class, 'IF(p.type = "alias", p.alias, p.id) = pl.page_id AND pl.language_code = "' . $langCode . '"', 'pl')
            ->andWhere(Page::FIELD_TEMPLATE . ' = :template:', ['template' => $this->getTemplate()]);
    }

    /**
     * @return Filters
     */
    public function getEmptyFilters(): Filters
    {
        return new PagesDataTableFilters();
    }

    /**
     * @inheritdoc
     */
    public function getJsData()
    {
        $jsData = parent::getJsData();

        $jsData['properties']['template'] = $this->getTemplate();

        return $jsData;
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
        return PageForm::class;
    }
}