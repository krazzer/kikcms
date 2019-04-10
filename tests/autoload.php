<?php
include_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../vendor/kiksaus/kikcms-core/src/functions.php';

setlocale(LC_ALL, 'nl_NL');

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("Helpers\\", __DIR__ . '/Helpers/', true);
$classLoader->addPsr4("Forms\\", __DIR__ . '/Forms/', true);
$classLoader->addPsr4("Models\\", __DIR__ . '/Models/', true);
$classLoader->addPsr4("DataTables\\", __DIR__ . '/DataTables/', true);
$classLoader->register();