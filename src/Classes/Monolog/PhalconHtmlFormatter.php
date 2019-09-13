<?php declare(strict_types=1);

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
    public function removeConfig($data)
    {
        if($data instanceof Injectable){
            unset($data->config);
            unset($data->applicationConfig);
        }

        try {
            foreach ($data as $property => $value) {
                if ( ! is_object($value) && ! is_array($value)) {
                    continue;
                }

                if (is_object($data)) {
                    $data->$property = $this->removeConfig($value);
                } else {
                    $data[$property] = $this->removeConfig($value);
                }
            }
        } catch(\Exception $exception){
            return $data;
        }

        return $data;
    }
}