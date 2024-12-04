<?php

use Phalcon\Mvc\Application;

$sitePath = __DIR__ . '/../TestSitePath/';

/** @var Application $app */
$app = include __DIR__ . '/../../boot/app.php';

$this->client->setServerParameter('HTTP_HOST', 'kikcmstest-phalcon5.dev');
$this->client->setServerParameter('PHP_SELF', '');

return $app;