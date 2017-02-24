<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Models\Field;
use KikCMS\Models\TemplateField;

class TemplateFieldForm extends DataForm
{
    /**
     * @return string
     */
    public function getModel(): string
    {
        return TemplateField::class;
    }

    public function initialize()
    {
        $allFields = Field::findAssoc();

        $this->addSelectField('field_id', 'Veld', $allFields);
        $this->addTextField('display_order', 'Volgorde');
    }
}