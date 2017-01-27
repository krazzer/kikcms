<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Forms\ProductForm;
use KikCMS\Models\DummyProducts;
use KikCMS\Models\Type;
use Phalcon\Mvc\Model\Query\Builder;

class Products extends DataTable
{
    /** @inheritdoc */
    protected $searchableFields = ['title', 'description', 'name'];

    /** @inheritdoc */
    protected $orderableFields = ['id' => 'pr.id'];

    /** @inheritdoc */
    protected $labels = 'dataTables.products';

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return DummyProducts::class;
    }

    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return ProductForm::class;
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultQuery()
    {
        $defaultQuery = new Builder();
        $defaultQuery->from(['pr' => $this->getModel()]);
        $defaultQuery->leftJoin(Type::class, 't.id = pr.category_id', 't');
        $defaultQuery->columns(['pr.id', 'pr.title', 'pr.price', 'pr.stock', 'category' => 't.name', 'pr.description']);
        $defaultQuery->orderBy('title ASC');

        if ( ! $this instanceof SubProducts) {
            $defaultQuery->andWhere('parent_id IS NULL');
        }

        return $defaultQuery;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->setFieldFormatting('price', [$this, 'formatPrice']);
        $this->setFieldFormatting('sale', [$this, 'formatSale']);
        $this->setFieldFormatting('description', [$this, 'formatDescription']);
    }

    /**
     * @param $value
     * @return string
     */
    protected function formatPrice($value)
    {
        return '&euro;&nbsp;' . number_format($value, 2, ',', '.');
    }

    /**
     * @param $value
     * @return string
     */
    protected function formatSale($value)
    {
        return $value == 1 ? '<span style="color: green;" class="glyphicon glyphicon-ok"></span>' : '';
    }

    /**
     * @param $value
     * @return string
     */
    protected function formatDescription($value)
    {
        $value = html_entity_decode(strip_tags($value));

        if (mb_strlen($value) > 50) {
            return mb_substr($value, 0, 50) . '...';
        }

        return $value;
    }
}