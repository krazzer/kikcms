<?php

use Phalcon\Mvc\Application;

require(__DIR__ . '/../functions.php');
require(SITE_PATH . 'vendor/autoload.php');

$cli         = false;
$services    = require(__DIR__ . '/services.php');
$application = new Application($services);

$application->registerModules([
    "frontend" => [
        "className" => "KikCMS\\Modules\\Frontend",
        "path"      => __DIR__ . "/../src/Modules/Frontend.php",
    ],
    "backend"  => [
        "className" => "KikCMS\\Modules\\Backend",
        "path"      => __DIR__ . "/../src/Modules/Backend.php",
    ],
    "website"  => [
        "className" => "KikCMS\\Modules\\Website",
        "path"      => __DIR__ . "/../src/Modules/Backend.php",
    ],
]);

// make sure the errorHandler is initialized
$application->errorHandler;

echo $application->handle()->getContent();