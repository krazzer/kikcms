<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon;


use KikCMS\Config\KikCMSConfig;

class Loader extends \Phalcon\Autoload\Loader
{
    /**
     * @return string
     */
    public function getWebsiteSrcPath(): string
    {
        return $this->getNamespaces()[KikCMSConfig::NAMESPACE_WEBSITE][0];
    }

    /**
     * @return string
     */
    public function getCmsSrcPath(): string
    {
        return $this->getNamespaces()[KikCMSConfig::NAMESPACE_KIKCMS][0];
    }
}