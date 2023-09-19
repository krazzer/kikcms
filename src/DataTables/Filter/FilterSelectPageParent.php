<?php declare(strict_types=1);

namespace KikCMS\DataTables\Filter;


use KikCMS\Classes\DataTable\Filter\FilterSelect;
use KikCMS\Models\Page;
use Phalcon\Mvc\Model\Query\Builder;

class FilterSelectPageParent extends FilterSelect
{
    /**
     * @inheritdoc
     */
    public function applyFilter(Builder $builder, $value): void
    {
        $parentPage = Page::getById($value);
        $alias      = Page::ALIAS;

        $builder->andWhere("$alias.lft > :lft: AND $alias.rgt < :rgt:", [
            'lft' => $parentPage->lft,
            'rgt' => $parentPage->rgt,
        ]);
    }
}