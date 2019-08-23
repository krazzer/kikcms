<?php declare(strict_types=1);


namespace KikCMS\Services;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Models\TranslationKey;
use KikCMS\Services\Util\StringService;
use KikCmsCore\Classes\Model;
use KikCmsCore\Services\DbService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Relation;

/**
 * @property DbService $dbService
 * @property StringService $stringService
 */
class ModelService extends Injectable
{
    /**
     * Adds a relation for translating the given field, with <field>Key
     * @param Model $model
     * @param string $field
     */
    public function addTranslationRelation(Model $model, string $field)
    {
        $alias = $this->stringService->underscoresToCamelCase($field) . 'Key';
        $model->belongsTo($field, TranslationKey::class, TranslationKey::FIELD_ID, ['alias' => $alias]);
    }

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

        return $model->getModelsManager()->getRelationByAlias(get_class($model), $alias) ?: null;
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
     * @return Model[]
     */
    public function getObjects(string $className, array $ids): array
    {
        $query = (new Builder)->from($className)
            ->inWhere(DataTable::TABLE_KEY, $ids);

        return $this->dbService->getObjects($query);
    }
}