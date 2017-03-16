<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Forms\PageForm;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use Phalcon\Mvc\Model\Query\Builder;

class Pages extends DataTable
{
    /** @inheritdoc */
    protected $jsClass = 'PagesDataTable';

    /** @inheritdoc */
    protected $searchableFields = ['name'];

    /** @inheritdoc */
    protected $orderableFields = ['id' => 'p.id'];

    /** @inheritdoc */
    protected $labels = 'dataTables.pages';

    /** @inheritdoc */
    protected $preLoadWysiwygJs = true;

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

    /**
     * @inheritdoc
     */
    protected function getDefaultQuery()
    {
        $defaultQuery = new Builder();
        $defaultQuery->from(['p' => $this->getModel()]);
        $defaultQuery->leftJoin(PageLanguage::class, 'p.id = pl.page_id', 'pl');
        $defaultQuery->orderBy('IFNULL(p.lft, 99999 + IFNULL(p.display_order, 99999)) asc');
        $defaultQuery->columns([
            'pl.name', 'p.id', 'p.display_order', 'p.level', 'p.lft', 'p.rgt', 'p.type', 'p.parent_id',
            'p.menu_max_level'
        ]);

        return $defaultQuery;
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
     * @return string
     */
    protected function formatName($value)
    {
        return '<span class="name">' . $value . '</span>';
    }
}