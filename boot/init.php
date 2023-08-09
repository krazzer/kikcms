<?php

// disable deprecation messages until https://github.com/phalcon/cphalcon/issues/16263# is resolved
error_reporting(E_ALL ^ E_DEPRECATED);

use Phalcon\Application\AbstractApplication;

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

/** @var AbstractApplication $application */
$application = include __DIR__ . '/app.php';

echo $application->handle($_SERVER["REQUEST_URI"])->getContent();