<?php

use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\Finder\FileHashService;
use KikCMS\Services\LanguageService;
use Phalcon\Cli\Task;

/**
 * @property FileHashService $fileHashService
 * @property LanguageService $languageService
 * @property PageRearrangeService $pageRearrangeService
 */
class MainTask extends Task
{
    public function mainAction()
    {
        echo "This is the default task and the default action" . PHP_EOL;
    }

    public function updateNestedSetAction()
    {
        $this->pageRearrangeService->updateNestedSet();
    }

    public function updateMissingFileHashesAction()
    {
        $this->fileHashService->updateMissingHashes();
    }
}