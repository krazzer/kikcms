<?php

namespace KikCMS\Classes\DataTable\Filter;


use Phalcon\Mvc\Model\Query\Builder;

/**
 * DataTable filter using HTML select
 */
class FilterSelect extends Filter
{
    /** @var array */
    private $options = [];

    /**
     * @param string $field
     * @param string $label
     * @param array $options
     * @param string|null $alias
     */
    public function __construct(string $field, string $label, array $options, $alias = null)
    {
        $this->field   = $field;
        $this->label   = $label;
        $this->options = $options;
        $this->alias   = $alias;
    }

    /**
     * @inheritdoc
     */
    public function applyFilter(Builder $builder, $value)
    {
        $valueKey = $this->field . '_filterselect_value';

        $builder->andWhere($this->getFieldWithAlias() . ' = :' . $valueKey .':', [
            $valueKey => $value,
        ]);
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return FilterSelect
     */
    public function setOptions(array $options): FilterSelect
    {
        $this->options = $options;
        return $this;
    }
}