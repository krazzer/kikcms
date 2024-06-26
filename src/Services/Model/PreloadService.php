<?php

namespace KikCMS\Services\Model;

use KikCMS\Classes\Phalcon\Injectable;
use KikCmsCore\Classes\Model;
use KikCmsCore\Classes\ObjectList;
use KikCmsCore\Classes\ObjectMap;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Relation;

class PreloadService extends Injectable
{
    /**
     * @param array|ObjectList $objects
     * @param array|string $relationAlias
     * @param callable|string|null $pathToChild
     * @param null $order
     * @return void
     */
    public function preload(ObjectList|array $objects, array|string $relationAlias, callable|string $pathToChild = null,
                            $order = null): void
    {
        $idList   = [];
        $relation = null;

        foreach ($objects as $object) {
            if ( ! $object = $this->getObject($object, $pathToChild)) {
                continue;
            }

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

        $relatedObjectMap = $this->getRelatedObjects($idList, $relation, $order);

        foreach ($objects as $object) {
            $field = $relation->getFields();

            if ( ! $object = $this->getObject($object, $pathToChild)) {
                continue;
            }

            if ( ! $relationId = $object->$field) {
                continue;
            }

            if ($relation->getType() == Relation::HAS_MANY) {
                $object->$relationAlias = new ObjectMap;
            } else {
                $object->$relationAlias = null;
            }

            if ( ! array_key_exists($relationId, $relatedObjectMap)) {
                continue;
            }

            $relatedObject = $relatedObjectMap[$relationId];

            if ($relation->getType() == Relation::HAS_MANY) {
                $object->$relationAlias = new ObjectMap($relatedObject);
            } else {
                $object->$relationAlias = $relatedObject;
            }
        }
    }

    /**
     * @param array $idMap
     * @param Relation $relation
     * @param null $order
     * @return Model[]
     */
    private function getRelatedObjects(array $idMap, Relation $relation, $order = null): array
    {
        $keyField = $relation->getReferencedFields();

        $query = (new Builder)
            ->from($relation->getReferencedModel())
            ->inWhere($keyField, $idMap);

        if ($order) {
            $query->orderBy($order);
        }

        $objects = $this->dbService->getObjects($query);

        $objectMap = [];

        if ($relation->getType() == Relation::HAS_MANY) {
            foreach ($objects as $object) {
                $objectMap[$object->$keyField][] = $object;
            }
        } else {
            foreach ($objects as $object) {
                $objectMap[$object->$keyField] = $object;
            }
        }

        return $objectMap;
    }

    /**
     * @param object $object
     * @param callable|string|null $pathToChild
     * @return object|null
     */
    private function getObject(object $object, callable|string $pathToChild = null): ?object
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