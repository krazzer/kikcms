<?php declare(strict_types=1);

namespace KikCMS\Classes\Monolog;


use Exception;
use KikCMS\Classes\Phalcon\IniConfig;
use Monolog\Formatter\HtmlFormatter;
use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Di;

/**
 * Filters out the config contents in error output, as it is contained in every Injectable class
 */
class PhalconHtmlFormatter extends HtmlFormatter
{
    /**
     * Attempt to filter out DB password from error messages
     * @inheritDoc
     */
    public function format(array $record)
    {
        try {
            /** @var IniConfig $config */
            $config = Di::getDefault()->get('config');

            $record['message'] = str_replace($config->database->password, '******', $record['message']);
            $record['message'] = str_replace(substr($config->database->password, 0, 15), '******', $record['message']);
        } catch (Exception $exception) {
        }

        return parent::format($record);
    }

    /**
     * @inheritdoc
     */
    protected function toJson($data, $ignoreErrors = false)
    {
        if (is_object($data) || is_array($data)) {
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
        if ($data instanceof Injectable) {
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
        } catch (Exception $exception) {
            return $data;
        }

        return $data;
    }
}