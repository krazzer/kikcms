<?php

use KikCMS\Services\Services;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\FactoryDefault\Cli;

$config    = new Ini(SITE_PATH . 'vendor/kiksaus/kikcms/config/config.ini');
$configDev = new Ini(SITE_PATH . 'vendor/kiksaus/kikcms/config/config.dev.ini');

$configSiteFile    = SITE_PATH . 'config/config.ini';
$configSiteDevFile = SITE_PATH . 'config/config.dev.ini';

if ( ! is_readable($configSiteFile)) {
    throw new Exception('No config file found! Should be present at ' . $configSiteFile);
}

$siteConfig = new Ini($configSiteFile);
$config->merge($siteConfig);

if (is_readable($configSiteDevFile)) {
    $config->merge($configDev);

    $configSiteDev = new Ini($configSiteDevFile);
    $config->merge($configSiteDev);
}

$loader = (new \Phalcon\Loader())
    ->registerNamespaces([
        "Website" => SITE_PATH . "app/",
        "KikCMS"  => __DIR__ . "/../src/",
    ])
    ->registerDirs([
        __DIR__ . "/../src/Tasks"
    ])
    ->register();

if($cli){
    class ApplicationServices extends Cli {}
} else {
    class ApplicationServices extends FactoryDefault{}
}

return new Services($config);