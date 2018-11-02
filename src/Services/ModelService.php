<?php


namespace KikCMS\Services;


use KikCmsCore\Classes\Model;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Relation;

class ModelService extends Injectable
{
    /**
     * @param Model|string $model
     * @param string $alias
     * @return Relation|null
     */
    public function getRelation($model, string $alias): ?Relation
    {
        if( ! $model instanceof Model){
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
}