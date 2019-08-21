<?php declare(strict_types=1);


namespace KikCMS\Services\WebForm;


use KikCMS\Config\DataFormConfig;
use KikCmsCore\Classes\Model;
use KikCmsCore\Services\DbService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Relation;

/**
 * Handles DataForm fields that have relations in their keys like: "person:name", called 'RelationKeys'
 *
 * @property DbService $dbService
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
     */
    public function set(Model $model, string $relationKey, $value, string $langCode = null)
    {
        $relationKey = $this->replaceLangCode($relationKey, $langCode);

        $parts = explode(DataFormConfig::RELATION_KEY_SEPARATOR, $relationKey);

        $this->createMissingRelations($model, $parts);

        switch (count($parts)) {
            case 2:
                list($part1, $part2) = $parts;

                $relation = $this->getRelation($model, $part1);

                if ($relation->getType() == Relation::HAS_MANY) {
                    $this->storeHasManyRelation($model, $part1, $part2, $value);
                } else {
                    $model->$part1->$part2 = $this->dbService->toStorage($value);
                }

            break;
            case 3:
                list($part1, $part2, $part3) = $parts;
                $model->$part1->$part2->$part3 = $this->dbService->toStorage($value);
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
    public function get(Model $model, string $relationKey, string $langCode = null)
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
            break;
            case 3:
                list($part1, $part2, $part3) = $parts;
                return @$model->$part1->$part2->$part3;
            break;
            case 4:
                list($part1, $part2, $part3, $part4) = $parts;
                return @$model->$part1->$part2->$part3->$part4;
            break;
            case 5:
                list($part1, $part2, $part3, $part4, $part5) = $parts;
                return @$model->$part1->$part2->$part3->$part4->$part5;
            break;
        }

        return null;
    }

    /**
     * Check if any relation objects are missing, and create them
     *
     * @param Model $model
     * @param array $parts
     */
    private function createMissingRelations(Model $model, array $parts)
    {
        /** @var Model $currentModel */
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
    private function createMissingRelation(Model $model, string $property)
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
     * @return mixed
     */
    private function getValueForHasMany(Model $model, string $relation, string $field)
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
    private function storeHasManyRelation(Model $model, string $relationField, string $field, $value)
    {
        $relation = $this->getRelation($model, $relationField);

        $relatedObjects = [];

        foreach ($value as $valueKey => $valueValue) {
            $referencedModel = $relation->getReferencedModel();

            /** @var Model $referencedModel */
            $referencedModel = new $referencedModel();

            if (strstr($field, DataFormConfig::RELATION_KEY_FIELD_SEPARATOR)) {
                list($keyField, $valueField) = explode(DataFormConfig::RELATION_KEY_FIELD_SEPARATOR, $field);

                if( ! $valueValue){
                    continue;
                }

                $referencedModel->$keyField   = $valueKey;
                $referencedModel->$valueField = $valueValue;
            } else {
                $referencedModel->$field = $valueValue;
            }

            $relatedObjects[] = $referencedModel;
        }

        $model->$relationField->delete();
        $model->$relationField = $relatedObjects;
    }
}