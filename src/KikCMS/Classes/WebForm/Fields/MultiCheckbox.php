<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\DataForm\FieldStorage\MultiCheckbox as MultiCheckboxStorage;
use KikCMS\Classes\WebForm\Field;

class MultiCheckbox extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_MULTI_CHECKBOX;
    }

    /**
     * @inheritdoc
     */
    public function table(string $table, $relationKey)
    {
        $fieldStorage = new MultiCheckboxStorage();
        $fieldStorage->setField($this);
        $fieldStorage->setTableModel($table);
        $fieldStorage->setRelationKey($relationKey);

        $this->form->addFieldStorage($fieldStorage);

        return $this;
    }
}