<?php

use KikCMS\Services\Pages\UrlService;
use Phalcon\Cli\Task;

/**
 * @property UrlService $urlService
 */
class UrlTask extends Task
{
    /**
     * Create urls for all pages that lack them
     */
    public function createUrlsAction()
    {
        $pageIds = $this->urlService->getPageIdsWithoutUrl();

        foreach ($pageIds as $pageId) {
            $this->urlService->createUrlsForPageId($pageId);
        }
    }
}