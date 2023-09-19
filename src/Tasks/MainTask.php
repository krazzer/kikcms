<?php declare(strict_types=1);

namespace KikCMS\Tasks;

use KikCMS\Classes\Phalcon\Task;

class MainTask extends Task
{
    /**
     * Placeholder main action
     */
    public function mainAction(): void
    {
        $this->cliService->outputLine("This is the default task and the default action");
    }

    /**
     * Make sure lft, rgt and level are set for every page
     */
    public function updateNestedSetAction(): void
    {
        $this->pageRearrangeService->updateNestedSet();
    }

    /**
     * Update all finder_file records with the hash field
     */
    public function updateMissingFileHashesAction(): void
    {
        $this->fileHashService->updateMissingHashes();
    }

    /**
     * Walk through the public media folder to find and remove broken links
     */
    public function cleanUpBrokenLinksAction(): void
    {
        $this->fileRemoveService->cleanUpBrokenSymlinks();
    }

    /**
     * Clean up the vendor folder to keep only necessary files
     */
    public function cleanUpVendorAction(): void
    {
        $this->vendorCleanUpService->clean();
    }

    /**
     * Write redirects
     */
    public function writeRedirectsAction(): void
    {
        $this->redirectService->writeRedirects();
    }
}