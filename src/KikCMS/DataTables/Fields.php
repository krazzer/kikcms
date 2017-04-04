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
            'id'      => $this->translator->tl('id'),
            'name'    => $this->translator->tl('name'),
            'type_id' => $this->translator->tl('type'),
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