<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

$application = include __DIR__ . '/app.php';

echo $application->handle()->getContent();