<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Forms\TemplateForm;
use KikCMS\Models\Template;

class Templates extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return TemplateForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Template::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {

    }
}