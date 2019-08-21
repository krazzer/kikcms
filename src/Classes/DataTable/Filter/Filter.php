<?php declare(strict_types=1);

namespace KikCMS\Classes\DataTable\Filter;


use Phalcon\Mvc\Model\Query\Builder;

/**
 * Filters are used to filter on the data shown in the DataTable's table
 */
abstract class Filter
{
    /** @var string */
    protected $alias;

    /** @var string */
    protected $field;

    /** @var string */
    protected $label;

    /** @var mixed */
    protected $default;

    /**
     * @param Builder $builder
     * @param $value
     */
    public abstract function applyFilter(Builder $builder, $value);

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return Filter
     */
    public function setAlias(string $alias): Filter
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getFieldWithAlias(): string
    {
        return $this->alias ? $this->alias . '.' . $this->field : $this->field;
    }

    /**
     * @param string $field
     * @return Filter
     */
    public function setField(string $field): Filter
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return Filter
     */
    public function setLabel(string $label): Filter
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     * @return Filter
     */
    public function setDefault($default): Filter
    {
        $this->default = $default;
        return $this;
    }
}