<?php

namespace KikCMS\Services\Website;


use Exception;

class WebsiteService
{
    /**
     * Calls a method from the website, which may not exist
     *
     * @param $className
     * @param $methodName
     * @param array $arguments
     * @param bool $exceptionOnFail
     * @param bool $returnOnFail
     *
     * @return mixed
     * @throws Exception
     */
    public function callMethod($className, $methodName, array $arguments = [], $exceptionOnFail = false, $returnOnFail = false)
    {
        $className  = 'Website\\Classes\\' . $className;

        if ( ! class_exists($className)) {
            if($exceptionOnFail){
                throw new Exception('Class ' . $className . ' not found');
            } else {
                return $returnOnFail;
            }
        }

        $object = new $className();

        if ( ! method_exists($object, $methodName)) {
            if($exceptionOnFail) {
                throw new Exception('Method ' . $className . '::' . $methodName . ' not found');
            } else {
                return $returnOnFail;
            }
        }

        return call_user_func_array(array($object, $methodName), $arguments);
    }
}