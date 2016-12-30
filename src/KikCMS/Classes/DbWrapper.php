<?php

namespace KikCMS\Classes;

use Phalcon\Db;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\ResultInterface;

class DbWrapper
{
    /** @var Mysql */
    private $db;

    public function __construct(Mysql $dbAdapter)
    {
        $this->db = $dbAdapter;
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
        return $this->queryRows($query)[0];
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

    public function update(string $table, array $set, array $where)
    {
        $where = array_map(function($key, $value){ return $key . ' = ' .$value; }, array_keys($where), array_values($where));
        $where = implode(' AND ', $where);

        return $this->db->update($table, array_keys($set), array_values($set), $where);
    }
}