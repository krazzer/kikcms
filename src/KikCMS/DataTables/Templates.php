<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Forms\TemplateForm;
use KikCMS\Models\Template;

class Templates extends DataTable
{
    /** @inheritdoc */
    protected $sortable = true;

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
    public function getLabels(): string
    {
        return 'dataTables.templates';
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
    protected function getTableFieldMap(): array
    {
        return [
            'id'   => $this->translator->tl('id'),
            'name' => $this->translator->tl('name'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {

    }
}