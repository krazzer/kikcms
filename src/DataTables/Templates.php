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
    public function getLabels(): array
    {
        return [
            $this->translator->tl('dataTables.templates.singular'),
            $this->translator->tl('dataTables.templates.plural')
        ];
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
            'file' => $this->translator->tl('file'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {

    }
}