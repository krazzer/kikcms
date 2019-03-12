<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\Translator;
use KikCMS\Models\FinderFile;
use KikCMS\ObjectLists\PageLanguageMap;
use KikCmsCore\Services\DbService;
use KikCMS\Models\Page;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguageContent;
use KikCMS\Models\PageLanguage;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property DbService $dbService
 * @property Translator $translator
 * @property TemplateService $templateService
 */
class PageContentService extends Injectable
{
    /** @var array */
    private $localPageVariablesCache = [];

    /**
     * Check if the image is linked to any page, or used in a rich-text field
     *
     * @param FinderFile $file
     * @return PageLanguageMap
     */
    public function fileIsLinked(FinderFile $file): PageLanguageMap
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

        if ($wysiwygFieldKeys) {
            $query = (new Builder)
                ->from(['pl' => PageLanguage::class])
                ->leftJoin(PageLanguageContent::class, 'plc.page_id = pl.page_id', 'plc')
                ->inWhere(PageLanguageContent::FIELD_FIELD, $wysiwygFieldKeys)
                ->andWhere('pl.' . PageLanguage::FIELD_LANGUAGE_CODE . ' = :code:', ['code' => $languageCode])
                ->andWhere('plc.' . PageLanguageContent::FIELD_LANGUAGE_CODE . ' = :code:', ['code' => $languageCode])
                ->andWhere(PageLanguageContent::FIELD_VALUE . ' LIKE :search:', ['search' => '%/finder/file/' . $file->getId() . '%'])
                ->groupBy('pl.page_id');

            /** @var PageLanguage[] $pageLanguages */
            $pageLanguages = $this->dbService->getObjects($query);

            foreach ($pageLanguages as $pageLanguage){
                $pageLangMap->add($pageLanguage, $pageLanguage->getPageId());
            }
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