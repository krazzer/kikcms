<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Hidden;

/**
 * @deprecated use KeyedDataTableField instead
 */
class DataTableField extends Field
{
    /** @var DataTable */
    private $dataTable;

    /** @var string */
    private $renderedDataTable;

    /**
     * @param DataTable $dataTable
     * @param string $label
     */
    public function __construct(DataTable $dataTable, string $label)
    {
        $element = (new Hidden('dt'))
            ->setLabel($label)
            ->setDefault($dataTable->getInstance());

        $this->dataTable = $dataTable;
        $this->element   = $element;
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
    public function getDataTable(): DataTable
    {
        return $this->dataTable;
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