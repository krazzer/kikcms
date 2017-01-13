<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;

class Autocomplete extends Field
{
    /** @var string */
    private $sourceTableModel;

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_AUTOCOMPLETE;
    }

    /**
     * @return string
     */
    public function getSourceTableModel(): string
    {
        return $this->sourceTableModel;
    }

    /**
     * @param string $sourceTableModel
     */
    public function setSourceTableModel(string $sourceTableModel)
    {
        $this->sourceTableModel = $sourceTableModel;
    }
}