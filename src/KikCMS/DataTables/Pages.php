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
    protected $searchableFields = ['title'];

    /** @inheritdoc */
    protected $orderableFields = ['id' => 'p.id'];

    /** @inheritdoc */
    protected $labels = 'dataTables.pages';

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
        $defaultQuery->columns(['p.id', 'pl.name']);
        $defaultQuery->leftJoin(PageLanguage::class, 'p.id = pl.page_id', 'pl');

        return $defaultQuery;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
    }
}