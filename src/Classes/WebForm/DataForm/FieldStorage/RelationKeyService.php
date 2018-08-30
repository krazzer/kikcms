<?php


namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;


use Exception;
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
     */
    public function set(Model $model, string $relationKey, $value)
    {
        $parts = explode(DataFormConfig::RELATION_KEY_SEPARATOR, $relationKey);

        $this->createMissingRelations($model, $parts);

        switch (count($parts)) {
            case 2:
                list($part1, $part2) = $parts;
                $model->$part1->$part2 = $value;
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
     *
     * @param Model $model
     * @param string $relationKey
     * @return mixed
     */
    public function get(Model $model, string $relationKey)
    {
        $parts = explode(DataFormConfig::RELATION_KEY_SEPARATOR, $relationKey);

        switch (count($parts)) {
            case 2:
                list($part1, $part2) = $parts;
                return $model->$part1->$part2;
            break;
            case 3:
                list($part1, $part2, $part3) = $parts;
                return $model->$part1->$part2->$part3;
            break;
            case 4:
                list($part1, $part2, $part3, $part4) = $parts;
                return $model->$part1->$part2->$part3->$part4;
            break;
            case 5:
                list($part1, $part2, $part3, $part4, $part5) = $parts;
                return $model->$part1->$part2->$part3->$part4->$part5;
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

        if ( ! $relation = $this->getRelation($model, $property)) {
            throw new Exception("Relation '$property' is missing from " . get_class($model));
        }

        $relatedModelClass = $relation->getReferencedModel();

        $model->$property = new $relatedModelClass();
    }

    /**
     * Get all defined 1-1 relations for a model
     *
     * @param Model $model
     * @return Relation[]
     */
    private function getRelations(Model $model): array
    {
        $hasOneRelations    = $model->getModelsManager()->getHasOne($model);
        $belongsToRelations = $model->getModelsManager()->getBelongsTo($model);

        return array_merge($hasOneRelations, $belongsToRelations);
    }

    /**
     * Get a defined relation by alias
     *
     * @param Model $model
     * @param string $property
     * @return Relation
     */
    private function getRelation(Model $model, string $property): ?Relation
    {
        $relations = $this->getRelations($model);

        foreach ($relations as $relation) {
            $options = $relation->getOptions();

            if( ! array_key_exists('alias', $options)){
                continue;
            }

            if ($options['alias'] === $property) {
                return $relation;
            }
        }

        return null;
    }
}