<?php

use KikCMS\Classes\Phalcon\Loader;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Services\Services;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\FactoryDefault\Cli;

if ( ! defined('SITE_PATH')) {
    throw new Exception('constant SITE_PATH is missing');
}

$sitePath = SITE_PATH;

$configFile     = $sitePath . 'vendor/kiksaus/kikcms/config/config.ini';
$configSiteFile = $sitePath . 'config/config.ini';
$configEnvFile  = $sitePath . 'env/config.ini';

if ( ! is_readable($configSiteFile)) {
    throw new Exception('No site config file found! Should be present at ' . $configSiteFile);
}

if ( ! is_readable($configEnvFile)) {
    throw new Exception('No env config file found! Should be present at ' . $configEnvFile);
}

$config     = new Ini($configFile);
$configSite = new Ini($configSiteFile);
$configEnv  = new Ini($configEnvFile);

$config->merge($configSite);
$config->merge($configEnv);

if ( ! isset($config->application->path)) {
    $config->application->path = $sitePath;
}

$cmsPath = $config->application->cmsPath = dirname(__DIR__) . "/";

/** @var Loader $loader */
$loader = (new Loader)
    ->registerNamespaces([
        KikCMSConfig::NAMESPACE_WEBSITE => $sitePath . 'app/',
        KikCMSConfig::NAMESPACE_KIKCMS  => $cmsPath . 'src/',
    ])
    ->registerDirs([
        $sitePath . 'app/Tasks',
        $cmsPath . 'src/Tasks',
    ])
    ->register();

if ($cli) {
    class ApplicationServices extends Cli{}
} else {
    class ApplicationServices extends FactoryDefault{}
}

return new Services($config, $loader);