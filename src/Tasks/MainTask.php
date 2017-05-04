<?php

use KikCMS\Services\LanguageService;
use Phalcon\Cli\Task;

/**
 * @property LanguageService $languageService
 */
class MainTask extends Task
{
    public function mainAction()
    {
        echo "This is the default task and the default action" . PHP_EOL;
    }
}