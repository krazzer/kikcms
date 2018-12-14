<?php


namespace KikCMS\Services;


use KikCMS\Classes\DataTable\DataTable;
use KikCmsCore\Classes\Model;
use KikCmsCore\Services\DbService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Relation;
use Phalcon\Mvc\Model\Resultset;

/**
 * @property DbService $dbService
 */
class ModelService extends Injectable
{
    /**
     * @param Model|string $model
     * @param string $alias
     * @return Relation|null
     */
    public function getRelation($model, string $alias): ?Relation
    {
        if ( ! $model instanceof Model) {
            $model = $this->getModelByClassName($model);
        }

        return $model->getModelsManager()->getRelationByAlias(get_class($model), $alias);
    }

    /**
     * @param string $className
     * @return Model
     */
    public function getModelByClassName(string $className): Model
    {
        return new $className();
    }

    /**
     * @param string $className
     * @param int $id
     * @return Model|null
     */
    public function getObject(string $className, int $id): ?Model
    {
        $model = $this->getModelByClassName($className);

        return $model::getById($id);
    }

    /**
     * @param string $className
     * @param array $ids
     * @return Model[]|Resultset
     */
    public function getObjects(string $className, array $ids): array
    {
        $query = (new Builder)->from($className)
            ->inWhere(DataTable::TABLE_KEY, $ids);

        $objects = [];
        $results = $this->dbService->getObjects($query);

        foreach ($results as $object){
            $objects[] = $object;
        }

        return $objects;
    }
}