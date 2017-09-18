<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguageContent;
use KikCMS\Models\PageLanguage;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property DbService $dbService
 */
class PageContentService extends Injectable
{
    /**
     * @param PageLanguage $pageLanguage
     * @return array
     */
    public function getVariablesByPageLanguage(PageLanguage $pageLanguage): array
    {
        $pageId   = $pageLanguage->getPageId();
        $langCode = $pageLanguage->getLanguageCode();

        $query = (new Builder)
            ->from(['pc' => PageContent::class])
            ->where('page_id = :pageId:', ['pageId' => $pageId])
            ->columns(['pc.field', 'pc.value']);

        $queryMultiLingual = (new Builder)
            ->from(['plc' => PageLanguageContent::class])
            ->where('page_id = :pageId: AND language_code = :langCode:', [
                'pageId' => $pageId, 'langCode' => $langCode
            ])
            ->columns(['plc.field', 'plc.value']);

        return $this->dbService->getAssoc($query) + $this->dbService->getAssoc($queryMultiLingual);
    }
}