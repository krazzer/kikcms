<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Translator;
use KikCMS\Forms\TemplateForm;
use KikCMS\Models\Template;

/**
 * @property Translator $translator
 */
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
            'id'   => $this->translator->tlb('id'),
            'name' => $this->translator->tlb('name'),
            'file' => $this->translator->tlb('file'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {

    }
}