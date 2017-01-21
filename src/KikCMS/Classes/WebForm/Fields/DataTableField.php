<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element;

class DataTableField extends Field
{
    /** @var DataTable */
    private $dataTable;

    /**
     * @param Element $element
     * @param DataTable $dataTable
     */
    public function __construct(Element $element, DataTable $dataTable)
    {
        parent::__construct($element);

        $this->dataTable = $dataTable;
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
}