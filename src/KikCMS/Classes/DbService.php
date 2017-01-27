<?php

namespace KikCMS\Classes;

use KikCMS\Classes\Model\Model;
use Phalcon\Db;
use Phalcon\Db\ResultInterface;
use Phalcon\Di\Injectable;

class DbService extends Injectable
{
    /**
     * @param string $model
     * @param array $where
     *
     * @return bool
     */
    public function delete(string $model, array $where)
    {
        $table        = $this->getTableForModel($model);
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

        if ( ! $whereClauses) {
            return true;
        }

        return $this->db->delete($table, implode(' AND ', $whereClauses));
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

        if( ! $result){
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

        $result->setFetchMode(Db::FETCH_ASSOC);
        return $result->fetchAll();
    }

    /**
     * @param string $model
     * @param array $set
     * @param array $where
     *
     * @return bool
     */
    public function update(string $model, array $set, array $where)
    {
        $table = $this->getTableForModel($model);
        $where = array_map(function($key, $value){ return $key . ' = ' .$value; }, array_keys($where), array_values($where));
        $where = implode(' AND ', $where);

        return $this->db->update($table, array_keys($set), array_values($set), $where);
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
     * @return string
     */
    private function getTableForModel(string $model): string
    {
        /** @var Model $model */
        $model = new $model();
        return $model->getSource();
    }
}