<?php

namespace KikCMS\Classes\Model;

use Exception;
use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Mvc\Model\Resultset;

class Model extends PhalconModel
{
    const TABLE = null;
    const ALIAS = null;

    public function initialize()
    {
        if ( ! static::TABLE) {
            throw new Exception('const ' . static::class . '::TABLE must be set');
        }

        $this->setSource(static::TABLE);
    }

    /**
     * @inheritdoc
     *
     * @return Resultset
     */
    public static function find($parameters = null)
    {
        /** @var Resultset $resultSet */
        $resultSet = parent::find($parameters);

        return $resultSet;
    }

    /**
     * Alias of find, but will always return an assoc array base on the first two columns of the result
     * typically id => name
     *
     * @param $parameters
     * @return array
     */
    public static function findAssoc($parameters = null)
    {
        $results     = self::find($parameters)->toArray();
        $returnArray = [];

        foreach ($results as $result) {
            $keys = array_keys($result);

            $returnArray[$result[$keys[0]]] = $result[$keys[1]];
        }

        return $returnArray;
    }

    /**
     * @param $id
     *
     * @return Model|null
     */
    public static function getById($id)
    {
        return self::findFirst('id = ' . $id);
    }

    /**
     * @param string $name
     *
     * @return Model|null
     */
    public static function getByName(string $name)
    {
        return self::findFirst([
            "conditions" => "name = ?1",
            "bind"       => [1 => $name]
        ]);
    }

    /**
     * @return array
     */
    public static function getNameList()
    {
        $results = self::find();
        $names   = [];

        foreach ($results as $result) {
            $names[] = $result->name;
        }

        return $names;
    }
}