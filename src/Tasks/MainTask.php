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

    /**
     * @param array $params
     */
    public function testAction(array $params)
    {
        foreach($this->languageService->getLanguages() as $language){
            echo $language->name . PHP_EOL;
        }

        echo sprintf(
            "hello %s",
            $params[0]
        );

        echo PHP_EOL;



        echo sprintf(
            "best regards, %s",
            $params[1]
        );

        echo PHP_EOL;
    }
}