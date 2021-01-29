<?php

use Phalcon\Mvc\Application;

$sitePath = __DIR__ . '/../TestSitePath/';

/** @var Application $app */
$app = include __DIR__ . '/../../boot/app.php';

$this->client->setServerParameter('HTTP_HOST', 'kikcmstest.dev');

return $app;