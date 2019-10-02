<?php

use KikCMS\Classes\Phalcon\IniConfig;
use KikCMS\Classes\Phalcon\Loader;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Services\Services;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\FactoryDefault\Cli;

if ( ! isset($sitePath)) {
    throw new Exception('Variable $sitePath must be set');
}

$configFile = dirname(__DIR__) . '/config/config.ini';

$configSiteFile = $sitePath . 'config/config.ini';
$configEnvFile  = $sitePath . 'env/config.ini';

if ( ! is_readable($configSiteFile)) {
    throw new Exception('No site config file found! Should be present at ' . $configSiteFile);
}

if ( ! is_readable($configEnvFile)) {
    throw new Exception('No env config file found! Should be present at ' . $configEnvFile);
}

$config     = new IniConfig($configFile);
$configSite = new IniConfig($configSiteFile);
$configEnv  = new IniConfig($configEnvFile);

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

if( ! class_exists('ApplicationServices')) {
    if ($cli) {
        class ApplicationServices extends Cli{}
    } else {
        class ApplicationServices extends FactoryDefault{}
    }
}

return new Services($config, $loader);