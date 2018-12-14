<?php
include_once __DIR__ . '/../vendor/autoload.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("Helpers\\", __DIR__ . '/Helpers/', true);
$classLoader->register();