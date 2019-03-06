<?php

use KikCMS\Plugins\PlaceholderConverterPlugin;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Application;

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

require(SITE_PATH . 'vendor/autoload.php');
require(SITE_PATH . 'vendor/kiksaus/kikcms-core/src/functions.php');

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

// add application event manager
$eventsManager = new Manager();
$eventsManager->attach("application:beforeSendResponse", new PlaceholderConverterPlugin);
$application->setEventsManager($eventsManager);

// make sure the errorHandler is initialized
$application->errorHandler;

echo $application->handle()->getContent();