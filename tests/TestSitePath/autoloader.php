<?php
spl_autoload_register(function ($class) {
    if(substr($class, 0, 6) == 'KikCMS'){
        $class = str_replace('KikCMS\\', '', $class);
        $class = str_replace('\\', '/', $class);

        include('/kikcms/src/' . $class . '.php');
    }
});