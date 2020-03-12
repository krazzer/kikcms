<?php
declare(strict_types=1);

namespace KikCMS\Services;


use KikCMS\Classes\Phalcon\Injectable;

class CliService extends Injectable
{
    public function outputLine(string $text)
    {
        if( ! $this->config->application->showCliOutput){
            return;
        }

        echo "\033[0;33m" . $text . " files generated\033[0m" . PHP_EOL;
    }
}