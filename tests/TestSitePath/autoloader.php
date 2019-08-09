<?php
spl_autoload_register(function ($class) {
    if(substr($class, 0, 6) == 'KikCMS'){
        $class = str_replace('KikCMS\\', '', $class);
        $class = str_replace('\\', '/', $class);

        include('/kikcms/src/' . $class . '.php');
    }

    if(substr($class, 0, 7) == 'Website'){
        $class = str_replace('Website\\', '', $class);
        $class = str_replace('\\', '/', $class);

        include(__DIR__ . '/../TestSitePath/app/' . $class . '.php');
    }
});