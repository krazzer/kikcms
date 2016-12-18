<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');

use KikCMS\Services\Services;
use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini as ConfigIni;

require(SITE_PATH . 'vendor/autoload.php');

try {
    $config = new ConfigIni(SITE_PATH . 'vendor/kiksaus/kikcms/config/config.ini');

    $configSiteFile    = SITE_PATH . 'app/config/config.ini';
    $configSiteDevFile = SITE_PATH . 'app/config/config.dev.ini';

    if (!is_readable($configSiteFile)) {
        throw new Exception('No config file found! Should be present at ' . $configSiteFile);
    }

    $siteConfig = new ConfigIni($configSiteFile);
    $config->merge($siteConfig);

    if (is_readable($configSiteDevFile)) {
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

    $application = new Application(new Services($config));

    $application->registerModules([
        "site"   => [
            "className" => "Multiple\\Frontend\\Module",
            "path"      => "../apps/frontend/Module.php",
        ],
        "cms"    => [
            "className" => "Multiple\\Backend\\Module",
            "path"      => "../apps/backend/Module.php",
        ],
        "kikcms" => [
            "className" => "KikCMS\\Modules\\KikCMS",
            "path"      => __DIR__ . "/src/KikCMS/Modules/KikCMS.php",
        ]
    ]);

    echo $application->handle()->getContent();
} catch (Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
