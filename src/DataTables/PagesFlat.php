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
     * @return string|null
     */
    abstract function getTemplate(): ?string;

    /**
     * @inheritdoc
     */
    public function getDefaultQuery()
    {
        $langCode = $this->getFilters()->getLanguageCode();

        $pageLangJoin = 'IF(p.type = "alias", p.alias, p.id) = pl.page_id AND pl.language_code = "' . $langCode . '"';

        $query = parent::getDefaultQuery()->leftJoin(PageLanguage::class, $pageLangJoin, 'pl');

        if($template = $this->getTemplate()){
            $query->andWhere(Page::FIELD_TEMPLATE . ' = :t:', ['t' => $template]);
        }

        return $query;
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

        if($this->getTemplate()) {
            $jsData['properties']['template'] = $this->getTemplate();
        }

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