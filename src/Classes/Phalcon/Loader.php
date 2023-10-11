<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon;


use KikCMS\Config\KikCMSConfig;

class Loader extends \Phalcon\Autoload\Loader
{
    /**
     * @return string
     * @noinspection PhpParamsInspection
     */
    public function getWebsiteSrcPath(): string
    {
        return first($this->getNamespaces()[KikCMSConfig::NAMESPACE_WEBSITE . KikCMSConfig::NAMESPACE_SEPARATOR]);
    }

    /**
     * @return string
     * @noinspection PhpParamsInspection
     */
    public function getCmsSrcPath(): string
    {
        return first($this->getNamespaces()[KikCMSConfig::NAMESPACE_KIKCMS . KikCMSConfig::NAMESPACE_SEPARATOR]);
    }
}