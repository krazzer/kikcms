<?php

namespace KikCMS\Classes;

use KikCMS\Classes\Exceptions\DbForeignKeyDeleteException;
use KikCMS\Classes\Model\Model;
use KikCMS\Config\DbConfig;
use Phalcon\Db;
use Phalcon\Db\ResultInterface;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

class DbService extends Injectable
{
    /**
     * @param string $model
     * @param array $where
     * @return bool
     * @throws \Exception
     */
    public function delete(string $model, array $where)
    {
        $table       = $this->getTableForModel($model);
        $whereClause = $this->getWhereClauseByArray($where);

        if (empty($whereClause)) {
            return true;
        }

        try {
            return $this->db->delete($table, $whereClause);
        } catch (\Exception $e) {
            if ($e->errorInfo[1] == DbConfig::ERROR_CODE_FK_CONSTRAINT_FAIL) {
                throw new DbForeignKeyDeleteException();
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param string $value
     * @return string
     */
    public function escape(string $value)
    {
        return $this->db->escapeString($value);
    }

    /**
     * Execute query
     *
     * @param $string
     * @return ResultInterface
     */
    public function query($string)
    {
        return $this->db->query($string);
    }

    /**
     * Returns an array with an assoc array with results per row
     *
     * @param string $query
     * @return array
     */
    public function queryRows(string $query): array
    {
        $result = $this->db->query($query);

        $result->setFetchMode(Db::FETCH_ASSOC);
        $resultData = $result->fetchAll();

        if ( ! $resultData) {
            return [];
        }

        return $resultData;
    }

    /**
     * Returns an array with an assoc data for one row
     *
     * @param string $query
     * @return array
     */
    public function queryRow(string $query): array
    {
        $result = $this->queryRows($query);

        if ( ! $result) {
            return [];
        }

        return $result[0];
    }

    /**
     * Returns an assoc array where the first two fields are key and value
     *
     * @param string $query
     * @return array
     */
    public function queryAssoc(string $query): array
    {
        $result = $this->db->query($query);

        $result->setFetchMode(Db::FETCH_KEY_PAIR);
        return $result->fetchAll();
    }

    /**
     * @param string $model
     * @param array $set
     * @param array $where
     *
     * @return bool
     */
    public function update(string $model, array $set, array $where = [])
    {
        $table       = $this->getTableForModel($model);
        $whereClause = $this->getWhereClauseByArray($where);

        if (empty($whereClause)) {
            $whereClause = null;
        }

        return $this->db->update($table, array_keys($set), array_values($set), $whereClause);
    }

    /**
     * @param string $model
     * @param array $insert
     *
     * @return mixed
     */
    public function insert(string $model, array $insert)
    {
        $table = $this->getTableForModel($model);

        $this->db->insert($table, array_values($insert), array_keys($insert));

        return $this->db->lastInsertId();
    }

    /**
     * @param string $model
     * @return string|null
     */
    public function getAliasForModel(string $model)
    {
        /** @var Model $model */
        $model = new $model();

        return $model::ALIAS;
    }

    /**
     * Retrieve a single result from the given query
     *
     * @param Builder $query
     * @return string|null
     */
    public function getValue(Builder $query)
    {
        $columns = (array) $query->getColumns();

        if (count($columns) !== 1) {
            throw new \InvalidArgumentException('The query must request a single column');
        }

        $result = $query->getQuery()->execute();

        if ( ! count($result)) {
            return null;
        }

        return first($result->getFirst()->toArray());
    }

    /**
     * Retrieve an array with a single column from the given query
     *
     * @param Builder $query
     * @return string|null
     */
    public function getValues(Builder $query)
    {
        $columns = (array) $query->getColumns();

        if (count($columns) !== 1) {
            throw new \InvalidArgumentException('The query must request a single column');
        }

        $results = $query->getQuery()->execute()->toArray();

        foreach ($results as $i => $row) {
            $results[$i] = first($row);
        }

        return $results;
    }

    /**
     * @param string $model
     * @return string
     */
    private function getTableForModel(string $model): string
    {
        /** @var Model $model */
        $model = new $model();
        return $model->getSource();
    }

    /**
     * @param array $where
     * @return string
     */
    private function getWhereClauseByArray(array $where): string
    {
        $whereClauses = [];

        foreach ($where as $column => $condition) {
            if (is_array($condition)) {
                if ( ! empty($condition)) {
                    $whereClauses[] = $column . " IN (" . implode(',', $condition) . ")";
                }
            } else {
                $whereClauses[] = $column . ' = ' . $this->escape($condition);
            }
        }

        return implode(' AND ', $whereClauses);
    }
}