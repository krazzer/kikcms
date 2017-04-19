<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Translator;
use KikCMS\Forms\FieldForm;
use KikCMS\Models\Field;

/**
 * @property Translator $translator
 */
class Fields extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return FieldForm::class;
    }

    public function getLabels(): string
    {
        return 'dataTables.fields';
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Field::class;
    }

    /**
     * @inheritdoc
     */
    protected function getTableFieldMap(): array
    {
        return [
            'id'       => $this->translator->tlb('id'),
            'variable' => $this->translator->tlb('variable'),
            'name'     => $this->translator->tlb('name'),
            'type_id'  => $this->translator->tlb('type'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->setFieldFormatting('type_id', function ($value) {
            $typeMap = $this->translator->getContentTypeMap();

            return $typeMap[$value];
        });
    }
}