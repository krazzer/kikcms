<?php

use KikCMS\Services\Generator\GeneratorService;
use Phalcon\Cli\Task;

/**
 * Task used for code generation
 *
 * @property GeneratorService $generatorService
 */
class GenerateTask extends Task
{
    /**
     * Called by: kikcms generate models
     */
    public function modelsAction()
    {
        $this->generatorService->generate();
    }

    /**
     * Called by: kikcms generate model <table_name>
     * @param array $parameters
     */
    public function modelAction(array $parameters)
    {
        $this->generatorService->generateForTable($parameters[0]);
    }
}