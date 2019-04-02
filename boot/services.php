<?php

use KikCMS\Services\Services;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\FactoryDefault\Cli;

if ( ! defined('SITE_PATH')) {
    throw new Exception('constant SITE_PATH is missing');
}

$configFile     = SITE_PATH . 'vendor/kiksaus/kikcms/config/config.ini';
$configSiteFile = SITE_PATH . 'config/config.ini';
$configEnvFile  = SITE_PATH . 'env/config.ini';

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
    $config->application->path = SITE_PATH;
}

$cmsPath = $config->application->cmsPath = dirname(__DIR__) . "/";

$loader = (new \Phalcon\Loader())
    ->registerNamespaces([
        'Website' => SITE_PATH . 'app/',
        'KikCMS'  => $cmsPath . 'src/',
    ])
    ->registerDirs([
        $cmsPath . 'src/Tasks',
        SITE_PATH . 'app/Tasks',
    ])
    ->register();

if ($cli) {
    class ApplicationServices extends Cli{}
} else {
    class ApplicationServices extends FactoryDefault{}
}

return new Services($config, $loader);