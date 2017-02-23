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
    protected $searchableFields = ['name'];

    /** @inheritdoc */
    protected $orderableFields = ['id' => 'p.id'];

    /** @inheritdoc */
    protected $labels = 'dataTables.pages';

    /** @inheritdoc */
    public $indexView = 'datatables/page/index';

    /** @inheritdoc */
    public $tableView = 'datatables/page/table';

    protected function addAssets()
    {
        parent::addAssets();

        $this->view->assets->addCss('cmsassets/css/pagesDataTable.css');
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
        $defaultQuery->columns(['pl.name', 'IFNULL(p.lft, 999999) AS page_order', 'p.id', 'p.type']);
        $defaultQuery->leftJoin(PageLanguage::class, 'p.id = pl.page_id', 'pl');
        $defaultQuery->orderBy('page_order asc');

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