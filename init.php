<?php

use KikCMS\Services\Services;
use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini as ConfigIni;

require(__DIR__ . '/functions.php');
require(SITE_PATH . 'vendor/autoload.php');

$config    = new ConfigIni(SITE_PATH . 'vendor/kiksaus/kikcms/config/config.ini');
$configDev = new ConfigIni(SITE_PATH . 'vendor/kiksaus/kikcms/config/config.dev.ini');

$configSiteFile    = SITE_PATH . 'config/config.ini';
$configSiteDevFile = SITE_PATH . 'config/config.dev.ini';

if ( ! is_readable($configSiteFile)) {
    throw new Exception('No config file found! Should be present at ' . $configSiteFile);
}

$siteConfig = new ConfigIni($configSiteFile);
$config->merge($siteConfig);

if (is_readable($configSiteDevFile)) {
    $config->merge($configDev);

    $configSiteDev = new ConfigIni($configSiteDevFile);
    $config->merge($configSiteDev);
}

$loader = new \Phalcon\Loader();

$loader->registerDirs([
    SITE_PATH . $config->application->controllersDir,
    SITE_PATH . $config->application->pluginsDir,
    SITE_PATH . $config->application->libraryDir,
    SITE_PATH . $config->application->modelsDir,
    SITE_PATH . $config->application->formsDir
])->register();

$loader->registerNamespaces([
    "Website" => SITE_PATH . "app/",
    "KikCMS"  => __DIR__ . "/src/",
]);

$websiteServicesClass = 'Website\Classes\WebsiteServices';

if (class_exists($websiteServicesClass)) {
    $services = new $websiteServicesClass($config);
} else {
    $services = new Services($config);
}

$application = new Application($services);

$application->registerModules([
    "frontend" => [
        "className" => "KikCMS\\Modules\\Frontend",
        "path"      => __DIR__ . "/src/Modules/Frontend.php",
    ],
    "backend"  => [
        "className" => "KikCMS\\Modules\\Backend",
        "path"      => __DIR__ . "/src/Modules/Backend.php",
    ],
    "website"  => [
        "className" => "KikCMS\\Modules\\Website",
        "path"      => __DIR__ . "/src/Modules/Backend.php",
    ],
]);

// make sure the errorHandler is initialized
$application->errorHandler;

echo $application->handle()->getContent();