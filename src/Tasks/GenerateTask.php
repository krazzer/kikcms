<?php declare(strict_types=1);

namespace KikCMS\Tasks;

use KikCMS\Classes\Phalcon\Task;

/**
 * Task used for code generation
 */
class GenerateTask extends Task
{
    /**
     * Called by: kikcms generate models
     */
    public function modelsAction()
    {
        $filesGeneratedCount = $this->generatorService->generate();
        $this->cliService->outputLine($filesGeneratedCount . " files generated");
    }

    /**
     * Called by: kikcms generate model <table_name>
     * @param array $params
     */
    public function modelAction(array $params)
    {
        $this->generatorService->generateForTable($params[0]);
    }
}