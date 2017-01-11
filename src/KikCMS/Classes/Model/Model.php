<?php

namespace KikCMS\Classes\Model;

use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Mvc\Model\Resultset;

class Model extends PhalconModel
{
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
}