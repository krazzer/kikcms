<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Hidden;

class DataTableField extends Field
{
    /** @var string */
    private $class;

    /** @var string */
    private $renderedDataTable;

    /** @var DataTable */
    private $dataTable;

    /**
     * @param string $key
     * @param string $class
     * @param string $label
     */
    public function __construct(string $key, string $class, string $label)
    {
        $this->class = $class;
        $this->key   = $key;

        $this->element = (new Hidden($key))
            ->setLabel($label)
            ->setDefault($this->getDataTable()->getInstance());
    }

    /**
     * @return DataTable
     */
    public function getDataTable(): DataTable
    {
        if ( ! $this->dataTable) {
            $this->dataTable = new $this->class();
        }

        return $this->dataTable;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_DATA_TABLE;
    }

    /**
     * @return DataTable
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getRenderedDataTable(): string
    {
        return $this->renderedDataTable;
    }

    /**
     * @param string $renderedDataTable
     * @return DataTableField
     */
    public function setRenderedDataTable(string $renderedDataTable): DataTableField
    {
        $this->renderedDataTable = $renderedDataTable;
        return $this;
    }
}