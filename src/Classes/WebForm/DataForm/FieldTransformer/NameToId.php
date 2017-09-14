<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldTransformer;


use KikCMS\Classes\Model\Model;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer;
use KikCMS\Classes\WebForm\Fields\AutocompleteField;

/**
 * Transformer to convert an object's name to the corresponding id
 */
class NameToId extends FieldTransformer
{
    /** @var AutocompleteField */
    protected $field;

    /**
     * @inheritdoc
     */
    public function toStorage($value)
    {
        /** @var Model $sourceModel */
        $sourceModel = $this->field->getForm()->getModel();

        $model = $sourceModel::getByName($value);

        return $model->id;
    }

    /**
     * @inheritdoc
     */
    public function toDisplay($value)
    {
        /** @var Model $sourceModel */
        $sourceModel = $this->field->getForm()->getModel();

        $model = $sourceModel::getById($value);

        if( ! $model){
            return null;
        }

        return $model->name;
    }
}