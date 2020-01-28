<?php

$sitePath = __DIR__ . '/../TestSitePath/';

/** @var Phalcon\Application $app */
$app = include __DIR__ . '/../../boot/app.php';

$this->client->setServerParameter('HTTP_HOST', 'kikcmstest.dev');

return $app;