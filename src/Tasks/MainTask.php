<?php declare(strict_types=1);

namespace KikCMS\Tasks;

use KikCMS\Classes\Phalcon\Task;

class MainTask extends Task
{
    /**
     * Placeholder main action
     */
    public function mainAction()
    {
        $this->cliService->outputLine("This is the default task and the default action");
    }

    /**
     * Make sure lft, rgt and level are set for every page
     */
    public function updateNestedSetAction()
    {
        $this->pageRearrangeService->updateNestedSet();
    }

    /**
     * Update all finder_file records with the hash field
     */
    public function updateMissingFileHashesAction()
    {
        $this->fileHashService->updateMissingHashes();
    }

    /**
     * Clean up the vendor folder to keep only necessary files
     */
    public function cleanUpVendorAction()
    {
        $this->vendorCleanUpService->clean();
    }
}