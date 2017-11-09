<?php

namespace KikCMS\Classes\Model;

use Exception;
use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Mvc\Model\Resultset;
use ReflectionClass;

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
     * @return null|Model|mixed
     */
    public static function getById($id)
    {
        if( ! $id){
            return null;
        }

        return self::findFirst('id = ' . $id);
    }

    /**
     * @inheritdoc
     *
     * @return null|Model|mixed
     */
    public static function findFirst($parameters = null)
    {
        $object = parent::findFirst($parameters);

        if( ! $object){
            return null;
        }

        return $object;
    }

    /**
     * @param int[] $ids
     *
     * @return Resultset|array
     */
    public static function getByIdList(array $ids)
    {
        if( ! $ids){
            return [];
        }

        return self::find([
            'conditions' => 'id IN ({ids:array})',
            'bind'       => ['ids' => $ids]
        ]);
    }

    /**
     * @param string $name
     *
     * @return null|Model
     */
    public static function getByName(string $name): ?Model
    {
        return self::findFirst([
            "conditions" => "name = ?1",
            "bind"       => [1 => $name]
        ]);
    }

    /**
     * @return array
     */
    public static function getFields()
    {
        $fields = [];

        $oClass    = new ReflectionClass(get_called_class());
        $constants = $oClass->getConstants();

        foreach ($constants as $constant => $value) {
            if (strpos($constant, 'FIELD_') !== false) {
                $fields[] = $value;
            }
        }

        return $fields;
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

    /**
     * @return array
     */
    public static function getNameMap(): array
    {
        $results = self::find(['order' => 'name']);
        $names   = [];

        foreach ($results as $result) {
            $names[$result->id] = $result->name;
        }

        return $names;
    }

    /**
     * @inheritdoc
     */
    public function save($data = null, $whiteList = null)
    {
        $saved = parent::save($data, $whiteList);

        if($messages = $this->getMessages()){
           foreach ($messages as $message){
               throw new Exception($message);
           }
        }

        return $saved;
    }
}