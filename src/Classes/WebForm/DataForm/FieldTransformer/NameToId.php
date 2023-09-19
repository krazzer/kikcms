<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\DataForm\FieldTransformer;


use KikCmsCore\Classes\Model;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer;
use KikCMS\Classes\WebForm\Fields\AutocompleteField;

/**
 * Transformer to convert an object's name to the corresponding id
 * @property AutocompleteField $field
 */
class NameToId extends FieldTransformer
{
    /**
     * @inheritdoc
     */
    public function toStorage(mixed $value): mixed
    {
        /** @var Model $sourceModel */
        $sourceModel = $this->field->getForm()->getModel();

        $model = $sourceModel::getByName($value);

        return $model->id;
    }

    /**
     * @inheritdoc
     */
    public function toDisplay(mixed $value): mixed
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