<?php

use Phalcon\Cli\Console;

$sitePath = SITE_PATH;

require(__DIR__ . '/../../kikcms-core/src/functions.php');
require($sitePath . 'vendor/autoload.php');

$cli      = true;
$services = require(__DIR__ . '/services.php');
$console  = new Console($services);

$arguments = [];

foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments["task"] = $arg;
    } elseif ($k === 2) {
        $arguments["action"] = $arg;
    } elseif ($k >= 3) {
        $arguments["params"][] = $arg;
    }
}

try {
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
    exit(255);
}