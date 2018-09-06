<?php


namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;


use KikCMS\Config\DataFormConfig;
use KikCmsCore\Classes\Model;
use Phalcon\Di\Injectable;
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
        return strstr($key, DataFormConfig::RELATION_KEY_SEPARATOR);
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
                    $relatedObjects = [];

                    foreach ($value as $id){
                        $referencedModel = $relation->getReferencedModel();

                        /** @var Model $referencedModel */
                        $referencedModel = new $referencedModel();
                        $referencedModel->$part2 = $id;

                        $relatedObjects[] = $referencedModel;
                    }

                    $model->$part1->delete();
                    $model->$part1 = $relatedObjects;
                } else {
                    $model->$part1->$part2 = $value;
                }

            break;
            case 3:
                list($part1, $part2, $part3) = $parts;
                $model->$part1->$part2->$part3 = $value;
            break;
            case 4:
                list($part1, $part2, $part3, $part4) = $parts;
                $model->$part1->$part2->$part3->$part4 = $value;
            break;
            case 5:
                list($part1, $part2, $part3, $part4, $part5) = $parts;
                $model->$part1->$part2->$part3->$part4->$part5 = $value;
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
                    $returnValue = [];

                    foreach ($model->$part1 as $item) {
                        $returnValue[] = $item->$part2;
                    }

                    return $returnValue;
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
        if( ! $langCode){
            return $relationKey;
        }

        return str_replace(DataFormConfig::RELATION_KEY_LANGUAGE_CODE_PLACEHOLDER, ucfirst($langCode), $relationKey);
    }
}