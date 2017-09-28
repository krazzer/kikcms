<?php

namespace KikCMS\Classes\Monolog;


use Monolog\Formatter\HtmlFormatter;
use Phalcon\Di\Injectable;

/**
 * Filters out the config contents in error output, as it is contained in every Injectable class
 */
class PhalconHtmlFormatter extends HtmlFormatter
{
    /**
     * @inheritdoc
     */
    protected function toJson($data, $ignoreErrors = false)
    {
        if(is_object($data) || is_array($data)) {
            $data = $this->removeConfig($data);
        }

        return parent::toJson($data, $ignoreErrors);
    }

    /**
     * @param $data
     * @return mixed
     */
    private function removeConfig($data)
    {
        if($data instanceof Injectable){
            unset($data->config);
            unset($data->applicationConfig);
        }

        foreach ($data as $property => $value){
            if( ! is_object($value) && ! is_array($value)){
                continue;
            }

            if(is_object($data)){
                $data->$property = $this->removeConfig($value);
            } else {
                $data[$property] = $this->removeConfig($value);
            }
        }

        return $data;
    }
}