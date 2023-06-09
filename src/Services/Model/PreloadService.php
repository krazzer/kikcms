<?php

namespace KikCMS\Services\Model;

use KikCMS\Classes\Phalcon\Injectable;
use KikCmsCore\Classes\Model;
use KikCmsCore\Classes\ObjectMap;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Relation;

class PreloadService extends Injectable
{
    /**
     * @param ObjectMap $objectMap
     * @param array|string $relationAlias
     * @param callable|string|null $pathToChild
     * @return void
     */
    public function preload(ObjectMap $objectMap, $relationAlias, $pathToChild = null)
    {
        $idList   = [];
        $relation = null;

        foreach ($objectMap as $object) {
            $object = $this->getObject($object, $pathToChild);

            if ( ! $relation) {
                $relation = $this->modelService->getRelation($object, $relationAlias);
            }

            $field = $relation->getFields();

            if ($relationId = $object->$field) {
                $idList[] = $relationId;
            }
        }

        // no relation found, cannot retrieve it
        if ( ! $relation) {
            return;
        }

        $relatedObjectMap = $this->getRelatedObjects($idList, $relation);

        foreach ($objectMap as $object) {
            $field  = $relation->getFields();
            $object = $this->getObject($object, $pathToChild);

            if ( ! $relationId = $object->$field) {
                continue;
            }

            $object->$relationAlias = null;

            if ( ! array_key_exists($relationId, $relatedObjectMap)) {
                continue;
            }

            $relatedObject = $relatedObjectMap[$relationId];

            $object->$relationAlias = $relatedObject;
        }
    }

    /**
     * @param array $idMap
     * @param Relation $relation
     * @return Model[]
     */
    private function getRelatedObjects(array $idMap, Relation $relation): array
    {
        $keyField = $relation->getReferencedFields();

        $query = (new Builder)
            ->from($relation->getReferencedModel())
            ->inWhere($keyField, $idMap);

        $objects = $this->dbService->getObjects($query);

        $objectMap = [];

        foreach ($objects as $object) {
            $objectMap[$object->$keyField] = $object;
        }

        return $objectMap;
    }

    /**
     * @param object $object
     * @param callable|string|null $pathToChild
     * @return object
     */
    private function getObject(object $object, $pathToChild = null): object
    {
        if (is_callable($pathToChild)) {
            return $pathToChild($object);
        }

        if (is_string($pathToChild)) {
            return $object->$pathToChild();
        }

        return $object;
    }
}