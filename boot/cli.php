<?php

use KikCMS\Config\KikCMSConfig;
use Phalcon\Cli\Console;

if ( ! isset($sitePath)) {
    throw new Exception('Variable $sitePath must be set');
}

require($sitePath . 'vendor/kiksaus/kikcms-core/src/functions.php');
require($sitePath . 'vendor/autoload.php');

$cli      = true;
$services = require(__DIR__ . '/services.php');
$console  = new Console($services);

$arguments = [];

foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $className    = KikCMSConfig::NAMESPACE_PATH_TASKS . ucfirst($arg);
        $cmsClassName = KikCMSConfig::NAMESPACE_PATH_CMS_TASKS . ucfirst($arg);

        if ( ! class_exists($className . 'Task')) {
            $className = $cmsClassName;
        }

        $arguments["task"] = $className;
    } elseif ($k === 2) {
        $arguments["action"] = $arg;
    } elseif ($k >= 3) {
        $arguments["params"]['params'][] = $arg;
    }
}

try {
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(255);
}