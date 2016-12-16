<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs([
    SITE_PATH . $config->application->controllersDir,
    SITE_PATH . $config->application->pluginsDir,
    SITE_PATH . $config->application->libraryDir,
    SITE_PATH . $config->application->modelsDir,
    SITE_PATH . $config->application->formsDir
])->register();

$loader->registerClasses([
    'Services' => __DIR__ . '/../src/Services.php'
]);