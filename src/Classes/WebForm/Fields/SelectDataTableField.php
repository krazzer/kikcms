<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DataTable\SelectDataTable;
use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Hidden;

class SelectDataTableField extends Field
{
    /** @var SelectDataTable */
    private $dataTable;

    /** @var string */
    private $renderedDataTable;

    /**
     * @param string $key
     * @param DataTable $dataTable
     * @param $label
     */
    public function __construct(string $key, DataTable $dataTable, $label)
    {
        $element = (new Hidden($key))
            ->setLabel($label);

        $this->dataTable = $dataTable;
        $this->element   = $element;
        $this->key       = $key;
    }

    /**
     * @inheritdoc
     */
    public function getInput($value)
    {
        return (array) json_decode($value);
    }

    /**
     * @inheritdoc
     */
    public function getFormFormat($value)
    {
        return json_encode($value);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_SELECT_DATA_TABLE;
    }

    /**
     * @return SelectDataTable
     */
    public function getDataTable(): SelectDataTable
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
     * @return SelectDataTableField
     */
    public function setRenderedDataTable(string $renderedDataTable): SelectDataTableField
    {
        $this->renderedDataTable = $renderedDataTable;
        return $this;
    }
}