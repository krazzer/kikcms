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

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return [
            $this->translator->tl('dataTables.fields.singular'),
            $this->translator->tl('dataTables.fields.plural')
        ];
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
            'id'       => $this->translator->tl('id'),
            'variable' => $this->translator->tl('variable'),
            'name'     => $this->translator->tl('name'),
            'type_id'  => $this->translator->tl('type'),
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