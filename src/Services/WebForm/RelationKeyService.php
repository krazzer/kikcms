<?php declare(strict_types=1);


namespace KikCMS\Services\WebForm;


use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\DataFormConfig;
use KikCmsCore\Classes\Model;
use Phalcon\Mvc\Model\Relation;

/**
 * Handles DataForm fields that have relations in their keys like: "person:name", called 'RelationKeys'
 */
class RelationKeyService extends Injectable
{
    /**
     * Check if given key is a relation key
     *
     * @param string $key
     * @return bool
     */
    public function isRelationKey(string $key): bool
    {
        return (bool) strstr($key, DataFormConfig::RELATION_KEY_SEPARATOR);
    }

    /**
     * Set a Models' value by a relationKey
     *
     * @param Model $model
     * @param string $relationKey
     * @param mixed $value
     * @param string|null $langCode
     * @return array
     */
    public function set(Model $model, string $relationKey, mixed $value, string $langCode = null): array
    {
        $relationKey = $this->replaceLangCode($relationKey, $langCode);

        $parts = explode(DataFormConfig::RELATION_KEY_SEPARATOR, $relationKey);

        $relationToPreSave = [];

        // if the value is empty and the field is not set, remove the relation
        if (count($parts) == 2 && $parts[1] === '' && ! $value) {
            if ($model->{$parts[0]}) {
                $model->{$parts[0]}->delete();
            }

            return $relationToPreSave;
        }

        $this->createMissingRelations($model, $parts);

        switch (count($parts)) {
            case 2:
                list($part1, $part2) = $parts;

                $relation = $this->getRelation($model, $part1);

                if ($relation->getType() == Relation::HAS_MANY) {
                    $this->storeHasManyRelation($model, $part1, $part2, $value);
                } else {
                    $subModel = $model->$part1;

                    foreach ($relation->getOptions()['defaults'] ?? [] as $defaultKey => $defaultValue) {
                        $subModel->$defaultKey = $defaultValue;
                    }

                    $subModel->$part2 = $this->dbService->toStorage($value);
                    $model->$part1    = $subModel;

                    if ($relation->getType() == Relation::BELONGS_TO) {
                        $relationToPreSave[] = $part1;
                    }
                }

            break;
            case 3:
                list($part1, $part2, $part3) = $parts;
                $relation = $this->getRelation($model, $part1);

                /** @var Model $part1Model */
                $part1Model = $model->$part1;
                $part2Model = $part1Model->$part2;

                $part2relation = $this->getRelation($part1Model, $part2);
                $part2defaults = $part2relation->getOptions()['defaults'] ?? [];

                if ($part2Model === null) {
                    $part2ModelName = $part2relation->getReferencedModel();
                    $part2Model     = new $part2ModelName();

                    $field    = $part2relation->getFields();
                    $refField = $part2relation->getReferencedFields();

                    $part2Model->$refField = $part1Model->$field;

                    foreach ($part2defaults as $defaultKey => $defaultValue) {
                        $part2Model->$defaultKey = $defaultValue;
                    }

                    $part2Model->save();

                    $part1Model->$field = $part2Model->$refField;
                } elseif ( ! $part1Model->isRelationshipLoaded($part2)) {
                    foreach ($part2defaults as $defaultKey => $defaultValue) {
                        $part2Model->$defaultKey = $defaultValue;
                    }
                }

                $part2Model->$part3 = $this->dbService->toStorage($value);

                $part1Model->$part2 = $part2Model;
                $model->$part1      = $part1Model;

                if ($relation->getType() == Relation::BELONGS_TO) {
                    $relationToPreSave[] = $part1;
                    $relationToPreSave[] = $part1 . DataFormConfig::RELATION_KEY_SEPARATOR . $part2;
                }
            break;
            case 4:
                list($part1, $part2, $part3, $part4) = $parts;
                $model->$part1->$part2->$part3->$part4 = $this->dbService->toStorage($value);
            break;
            case 5:
                list($part1, $part2, $part3, $part4, $part5) = $parts;
                $model->$part1->$part2->$part3->$part4->$part5 = $this->dbService->toStorage($value);
            break;
        }

        return $relationToPreSave;
    }

    /**
     * Get a Models' relations' value by relationKey
     * Note that we use '@' to suppress notices. If it's not there, it's not there. Null will be returned if so.
     *
     * @param Model $model
     * @param string $relationKey
     * @param string|null $langCode
     * @return mixed
     */
    public function get(Model $model, string $relationKey, string $langCode = null): mixed
    {
        $relationKey = $this->replaceLangCode($relationKey, $langCode);

        $parts = explode(DataFormConfig::RELATION_KEY_SEPARATOR, $relationKey);

        switch (count($parts)) {
            case 2:
                list($part1, $part2) = $parts;

                $relation = $this->getRelation($model, $part1);

                if ($relation->getType() == Relation::HAS_MANY) {
                    return $this->getValueForHasMany($model, $part1, $part2);
                }

                return @$model->$part1->$part2;
            case 3:
                list($part1, $part2, $part3) = $parts;
                return @$model->$part1->$part2->$part3;
            case 4:
                list($part1, $part2, $part3, $part4) = $parts;
                return @$model->$part1->$part2->$part3->$part4;
            case 5:
                list($part1, $part2, $part3, $part4, $part5) = $parts;
                return @$model->$part1->$part2->$part3->$part4->$part5;
        }

        return null;
    }

    /**
     * @param string $model
     * @param string $relationKey
     * @return array [string|null model, string relationKey]
     */
    public function getLastModelAndKey(string $model, string $relationKey): array
    {
        if ( ! $this->hasMultipleRelations($relationKey)) {
            return [$model, $relationKey];
        }

        $parts = explode(DataFormConfig::RELATION_KEY_SEPARATOR, $relationKey);

        $lastModel = $model;
        $lastKey   = $relationKey;

        foreach ($parts as $alias) {
            // initialize the model so aliases can be fetched
            new $lastModel();

            $relation  = $this->modelsManager->getRelationByAlias($lastModel, $alias);
            $lastModel = $relation->getReferencedModel();
            $lastKey   = $alias;
        }

        return [$lastModel, $lastKey];
    }

    /**
     * @param Model $object
     * @param string $relationKey
     * @return Model|null
     */
    public function getLastRelatedObject(Model $object, string $relationKey): ?Model
    {
        if ( ! $this->hasMultipleRelations($relationKey)) {
            return $object;
        }

        $parts = explode(DataFormConfig::RELATION_KEY_SEPARATOR, $relationKey);

        array_pop($parts);

        $lastObject = $object;

        foreach ($parts as $alias) {
            $lastObject = $lastObject->$alias;
        }

        return $lastObject;
    }

    /**
     * Check if any relation objects are missing, and create them
     *
     * @param Model $model
     * @param array $parts
     */
    private function createMissingRelations(Model $model, array $parts): void
    {
        $currentModel = $model;

        foreach ($parts as $index => $part) {
            if ($index === last_key($parts)) {
                continue;
            }

            $this->createMissingRelation($currentModel, $part);
            $currentModel = $currentModel->$part;
        }
    }

    /**
     * Check if a relation is missing, and create it if so
     *
     * @param Model $model
     * @param string $property
     */
    private function createMissingRelation(Model $model, string $property): void
    {
        if ($model->$property) {
            return;
        }

        $relation     = $this->getRelation($model, $property);
        $relatedModel = $relation->getReferencedModel();

        $model->$property = new $relatedModel();
    }

    /**
     * @param Model $model
     * @param string $relation
     * @param string $field
     * @return array
     */
    private function getValueForHasMany(Model $model, string $relation, string $field): array
    {
        $returnValue = [];

        foreach ($model->$relation as $item) {
            if (strstr($field, DataFormConfig::RELATION_KEY_FIELD_SEPARATOR)) {
                list($keyField, $valueField) = explode(DataFormConfig::RELATION_KEY_FIELD_SEPARATOR, $field);

                $returnValue[$item->$keyField] = $item->$valueField;
            } else {
                $returnValue[] = $item->$field;
            }
        }

        return $returnValue;
    }

    /**
     * Get a defined relation by alias
     *
     * @param Model $model
     * @param string $alias
     * @return Relation
     */
    private function getRelation(Model $model, string $alias): Relation
    {
        return $this->modelsManager->getRelationByAlias(get_class($model), $alias);
    }

    /**
     * @param string $relationKey
     * @param string|null $langCode
     * @return string
     */
    private function replaceLangCode(string $relationKey, string $langCode = null): string
    {
        if ( ! $langCode) {
            return $relationKey;
        }

        return str_replace(DataFormConfig::RELATION_KEY_LANGUAGE_CODE_PLACEHOLDER, ucfirst($langCode), $relationKey);
    }

    /**
     * @param Model $model
     * @param string $relationField
     * @param string $field
     * @param $value
     */
    private function storeHasManyRelation(Model $model, string $relationField, string $field, $value): void
    {
        $relation = $this->getRelation($model, $relationField);

        $relatedObjects = [];

        // could be json from selectDataTable or multiCheckbox, then decode first
        $value = is_string($value) ? json_decode($value) : $value;

        if($value === null){
            $value = [];
        }

        foreach ($value as $valueKey => $valueValue) {
            $referencedModel = $relation->getReferencedModel();

            /** @var Model $referencedModel */
            $referencedModel = new $referencedModel();

            if (strstr($field, DataFormConfig::RELATION_KEY_FIELD_SEPARATOR)) {
                list($keyField, $valueField) = explode(DataFormConfig::RELATION_KEY_FIELD_SEPARATOR, $field);

                if ( ! $valueValue) {
                    continue;
                }

                $referencedModel->$keyField   = $valueKey;
                $referencedModel->$valueField = $valueValue;
            } else {
                $referencedModel->$field = $valueValue;
            }

            $relatedObjects[] = $referencedModel;
        }

        // if it's an array the related objects aren't stored yet
        if ( ! is_array($model->$relationField)) {
            $model->$relationField->delete();
        }

        $model->$relationField = $relatedObjects;
    }

    /**
     * @param string $relationKey
     * @return bool
     */
    private function hasMultipleRelations(string $relationKey): bool
    {
        return str_contains($relationKey, DataFormConfig::RELATION_KEY_SEPARATOR);
    }

    /**
     * @param Model $object
     * @param array $preSaveRelations
     * @return void
     */
    public function savePreSaveRelations(Model $object, array $preSaveRelations): void
    {
        foreach ($preSaveRelations as $preSaveRelation) {
            if (strstr($preSaveRelation, DataFormConfig::RELATION_KEY_SEPARATOR)) {
                $parts = explode(DataFormConfig::RELATION_KEY_SEPARATOR, $preSaveRelation);

                if (count($parts) == 2) {
                    list($part1, $part2) = $parts;
                    $object->$part1->$part2->save();
                }
            } else {
                $object->$preSaveRelation->save();
            }
        }
    }
}