<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Models\Field;
use KikCMS\Models\TemplateField;

class TemplateFieldForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return TemplateField::class;
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        $allFields = Field::findAssoc();

        $this->addSelectField('field_id', 'Veld', $allFields);
    }
}