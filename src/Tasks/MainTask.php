<?php declare(strict_types=1);

namespace KikCMS\Tasks;

use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\Finder\FileHashService;
use KikCMS\Services\VendorCleanUpService;
use Phalcon\Cli\Task;

/**
 * @property FileHashService $fileHashService
 * @property PageRearrangeService $pageRearrangeService
 * @property VendorCleanUpService $vendorCleanUpService
 */
class MainTask extends Task
{
    /**
     * Placeholder main action
     */
    public function mainAction()
    {
        echo "This is the default task and the default action" . PHP_EOL;
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