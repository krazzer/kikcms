<?php declare(strict_types=1);

namespace KikCMS\Services\Pages;

use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Models\File;
use KikCMS\ObjectLists\PageLanguageMap;
use KikCMS\Models\Page;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguageContent;
use KikCMS\Models\PageLanguage;
use Monolog\Logger;
use Phalcon\Mvc\Model\Query\Builder;

class PageContentService extends Injectable
{
    /** @var array */
    private $localPageVariablesCache = [];

    /**
     * Check if the image is linked to any page, or used in a rich-text field
     *
     * @param File $file
     * @return PageLanguageMap
     */
    public function getLinkedPageLanguageMap(File $file): PageLanguageMap
    {
        $languageCode     = $this->translator->getLanguageCode();
        $fileFieldKeys    = $this->templateService->getFileFieldKeys();
        $wysiwygFieldKeys = $this->templateService->getWysiwygFieldKeys();

        $pageLangMap = new PageLanguageMap();

        if ($fileFieldKeys) {
            $query = (new Builder)
                ->from(['pl' => PageLanguage::class])
                ->leftJoin(PageContent::class, 'pc.page_id = pl.page_id', 'pc')
                ->where(PageLanguage::FIELD_LANGUAGE_CODE . ' = :code:', ['code' => $languageCode])
                ->inWhere(PageContent::FIELD_FIELD, $fileFieldKeys)
                ->andWhere(PageContent::FIELD_VALUE . ' = :fileId:', ['fileId' => $file->getId()])
                ->groupBy('pl.page_id');

            $pageLangMap = $this->dbService->getObjectMap($query, PageLanguageMap::class, PageLanguage::FIELD_PAGE_ID);
        }

        if ( ! $wysiwygFieldKeys || $file->isFolder()) {
            return $pageLangMap;
        }

        $searchParams = [
            'public'  => '%/media/files/' . $file->getId() . '%',
            'private' => '%/media/files/' . $file->getHash() . '%',
        ];

        $query = (new Builder)
            ->from(['pl' => PageLanguage::class])
            ->leftJoin(PageLanguageContent::class, 'plc.page_id = pl.page_id', 'plc')
            ->inWhere(PageLanguageContent::FIELD_FIELD, $wysiwygFieldKeys)
            ->andWhere('pl.' . PageLanguage::FIELD_LANGUAGE_CODE . ' = :code:', ['code' => $languageCode])
            ->andWhere('plc.' . PageLanguageContent::FIELD_LANGUAGE_CODE . ' = :code:', ['code' => $languageCode])
            ->andWhere('plc.value LIKE :public: OR plc.value LIKE :private:', $searchParams)
            ->groupBy('pl.page_id');

        /** @var PageLanguage[] $pageLanguages */
        $pageLanguages = $this->dbService->getObjects($query);

        foreach ($pageLanguages as $pageLanguage) {
            $pageLangMap->add($pageLanguage, $pageLanguage->getPageId());
        }

        return $pageLangMap;
    }

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

        $pageContent         = $this->dbService->getAssoc($query);
        $pageContentLanguage = $this->dbService->getAssoc($queryMultiLingual);

        if ($intersections = array_intersect_key($pageContent, $pageContentLanguage)) {
            $this->logger->log(Logger::WARNING, 'The following fields have both multilingual and monolingual variables: ' .
                implode(', ', array_keys($intersections)) . '. Please remove them from the database in either ' .
                PageContent::TABLE . ' or ' . PageLanguageContent::TABLE);
        }

        $pageVariables = $pageContent + $pageContentLanguage;

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

    /**
     * @param array $pageIds
     * @param string $field
     * @return array [pageId => value]
     */
    public function getValueMap(array $pageIds, string $field): array
    {
        $query = (new Builder)
            ->columns([PageContent::FIELD_PAGE_ID, PageContent::FIELD_VALUE])
            ->from(PageContent::class)
            ->inWhere(PageContent::FIELD_PAGE_ID, $pageIds)
            ->andWhere(PageContent::FIELD_FIELD . ' = :field:', ['field' => $field]);

        return $this->dbService->getAssoc($query);
    }
}