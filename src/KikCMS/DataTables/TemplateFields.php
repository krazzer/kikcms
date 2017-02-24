<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Forms\TemplateFieldForm;
use KikCMS\Models\Field;
use KikCMS\Models\TemplateField;
use Phalcon\Mvc\Model\Query\Builder;

class TemplateFields extends DataTable
{
    protected $parentRelationKey = TemplateField::FIELD_TEMPLATE_ID;

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
    public function getModel(): string
    {
        return TemplateField::class;
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