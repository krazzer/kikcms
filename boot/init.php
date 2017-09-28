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
    ],
    "backend"  => [
        "className" => "KikCMS\\Modules\\Backend",
    ],
    "websiteFrontend"  => [
        "className" => "KikCMS\\Modules\\WebsiteFrontend",
    ],
    "websiteBackend"  => [
        "className" => "KikCMS\\Modules\\WebsiteBackend",
    ],
]);

// make sure the errorHandler is initialized
$application->errorHandler;

echo $application->handle()->getContent();