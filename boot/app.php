<?php

use KikCMS\Modules\Backend;
use KikCMS\Modules\Frontend;
use KikCMS\Modules\WebsiteBackend;
use KikCMS\Modules\WebsiteFrontend;
use KikCMS\Plugins\PlaceholderConverterPlugin;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Application;

if ( ! isset($sitePath)) {
    throw new Exception('Variable $sitePath must be set');
}

require_once($sitePath . 'vendor/autoload.php');
require_once($sitePath . 'vendor/kiksaus/kikcms-core/src/functions.php');

$cli         = false;
$services    = require(__DIR__ . '/services.php');
$application = new Application($services);

$application->registerModules([
    "frontend"        => ["className" => Frontend::class],
    "backend"         => ["className" => Backend::class],
    "websiteFrontend" => ["className" => WebsiteFrontend::class],
    "websiteBackend"  => ["className" => WebsiteBackend::class],
]);

// add application event manager
$eventsManager = new Manager();
$eventsManager->attach("application:beforeSendResponse", new PlaceholderConverterPlugin);
$application->setEventsManager($eventsManager);

// make sure the errorHandler is initialized
/** @noinspection PhpExpressionResultUnusedInspection */
$application->errorHandler;

return $application;