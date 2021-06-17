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
     * @param string $table
     */
    public function modelAction(string $table)
    {
        $this->generatorService->generateForTable($table);
    }
}