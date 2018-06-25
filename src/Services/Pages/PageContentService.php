<?php

namespace KikCMS\Services\Pages;

use KikCmsCore\Services\DbService;
use KikCMS\Models\Page;
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
    /** @var array */
    private $localPageVariablesCache = [];

    /**
     * @param PageLanguage $pageLanguage
     * @return array
     */
    public function getVariablesByPageLanguage(PageLanguage $pageLanguage): array
    {
        $pageId   = $pageLanguage->getPageId();
        $langCode = $pageLanguage->getLanguageCode();

        if (array_key_exists($pageId . $langCode, $this->localPageVariablesCache)) {
            return $this->localPageVariablesCache[$pageId . $langCode];
        }

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

        $pageVariables = $this->dbService->getAssoc($query) + $this->dbService->getAssoc($queryMultiLingual);

        $this->localPageVariablesCache[$pageId . $langCode] = $pageVariables;

        return $pageVariables;
    }

    /**
     * Get a non-translatable value from the PageContent
     *
     * @param Page $page
     * @param string $field
     *
     * @return null|string
     */
    public function getPageVariable(Page $page, string $field): ?string
    {
        $query = (new Builder)
            ->from(PageContent::class)
            ->where(PageContent::FIELD_FIELD . ' = :field:', ['field' => $field])
            ->andWhere(PageContent::FIELD_PAGE_ID . ' = :pageId:', ['pageId' => $page->getId()])
            ->columns([PageContent::FIELD_VALUE]);

        return $this->dbService->getValue($query);
    }
}