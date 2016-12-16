<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');

use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini as ConfigIni;

define('KIKCMS_PATH', __DIR__ . '/');

require(SITE_PATH . 'vendor/autoload.php');

try {
    // Read the configuration
    $config     = new ConfigIni(SITE_PATH . 'vendor/kiksaus/kikcms/config/config.ini');
    $siteConfig = new ConfigIni(SITE_PATH . 'app/config/config.ini');

    $config->merge($siteConfig);

    if (is_readable(SITE_PATH . 'app/config/config.dev.ini')) {
        $developmentConfig = new ConfigIni(SITE_PATH . 'app/config/config.dev.ini');
        $config->merge($developmentConfig);
    }

    /**
     * Auto-loader configuration
     */
    require __DIR__ . '/config/loader.php';

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
