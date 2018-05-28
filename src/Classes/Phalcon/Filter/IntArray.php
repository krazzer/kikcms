<?php


namespace KikCMS\Classes\Phalcon\Filter;


class IntArray
{
    /**
     * @param $intArray
     * @return int[]
     */
    public function filter($intArray): array
    {
        $intArray = (array) $intArray;

        foreach ($intArray as &$int){
            $int = (int) $int;
        }

        return $intArray;
    }
}