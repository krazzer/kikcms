<?php
declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;


use KikCMS\Classes\Phalcon\ConfigGroups\ApplicationConfig;
use KikCMS\Classes\Phalcon\ConfigGroups\CacheConfig;
use KikCMS\Classes\Phalcon\ConfigGroups\DatabaseConfig;
use KikCMS\Classes\Phalcon\ConfigGroups\DeveloperConfig;
use KikCMS\Classes\Phalcon\ConfigGroups\MediaConfig;
use KikCMS\Config\KikCMSConfig;
use Phalcon\Config\Adapter\Ini;

/**
 * @property ApplicationConfig $application
 * @property DeveloperConfig $developer
 * @property DatabaseConfig $database
 * @property MediaConfig $media
 * @property CacheConfig $cache
 */
class IniConfig extends Ini
{
    /**
     * @return bool
     */
    public function isDev(): bool
    {
        return $this->application->env == KikCMSConfig::ENV_DEV;
    }

    /**
     * @return bool
     */
    public function isProd(): bool
    {
        return $this->application->env == KikCMSConfig::ENV_PROD;
    }
}