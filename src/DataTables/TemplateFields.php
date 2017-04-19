<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Translator;
use KikCMS\Forms\TemplateFieldForm;
use KikCMS\Models\Field;
use KikCMS\Models\TemplateField;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property Translator $translator
 */
class TemplateFields extends DataTable
{
    /** @inheritdoc */
    protected $parentRelationKey = TemplateField::FIELD_TEMPLATE_ID;

    /** @inheritdoc */
    protected $sortable = true;

    /**
     * @inheritdoc
     */
    protected function getDefaultQuery()
    {
        $query = new Builder();
        $query->from(['tf' => $this->getModel()]);
        $query->join(Field::class, 'f.id = tf.field_id', 'f');
        $query->columns(['tf.id', 'f.name', 'f.type_id', 'tf.display_order']);

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return TemplateFieldForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): string
    {
        return 'dataTables.templateFields';
    }

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
    protected function getTableFieldMap(): array
    {
        return [
            'id'      => $this->translator->tlb('id'),
            'name'    => $this->translator->tlb('name'),
            'type_id' => $this->translator->tlb('type'),
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