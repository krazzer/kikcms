<?php

$_SERVER['HTTP_HOST'] = 'test.dev';

$sitePath = __DIR__ . '/../TestSitePath/';

/** @var Phalcon\Application $app */
$app = include __DIR__ . '/../../boot/app.php';

return $app;