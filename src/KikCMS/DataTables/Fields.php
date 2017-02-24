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
    public function getModel(): string
    {
        return Field::class;
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