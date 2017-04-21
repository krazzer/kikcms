<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\LanguageService;
use Phalcon\Di\Injectable;

/**
 * Service for handling Page Model objects
 *
 * @property DbService $dbService
 * @property LanguageService $languageService
 */
class PageLanguageService extends Injectable
{
    /**
     * @param Page $page
     * @param string|null $languageCode
     * @return PageLanguage|null
     */
    public function getByPage(Page $page, string $languageCode = null)
    {
        return $this->getByPageId($page->getId(), $languageCode);
    }

    /**
     * Get the PageLanguage by given pageId, using the default languageCode
     *
     * @param int $pageId
     * @param string|null $languageCode
     * @return PageLanguage|null
     */
    public function getByPageId(int $pageId, string $languageCode = null)
    {
        if ( ! $languageCode) {
            $languageCode = $this->languageService->getDefaultLanguageCode();
        }

        $pageLanguage = PageLanguage::findFirst([
            'conditions' => 'page_id = :pageId: AND language_code = :langCode:',
            'bind'       => [
                'pageId'   => $pageId,
                'langCode' => $languageCode
            ],
        ]);

        if ( ! $pageLanguage) {
            return null;
        }

        return $pageLanguage;
    }

    /**
     * @param array $pageMap
     * @param string $languageCode
     * @return PageLanguage[] (PageLanguageMap)
     */
    public function getByPageMap(array $pageMap, string $languageCode = null): array
    {
        if ( ! $languageCode) {
            $languageCode = $this->languageService->getDefaultLanguageCode();
        }

        $pageLanguages = PageLanguage::find([
            'conditions' => 'page_id IN ({ids:array}) AND language_code = :langCode:',
            'bind'       => ['ids' => array_keys($pageMap), 'langCode' => $languageCode]
        ]);

        $pageLanguageMap = [];

        foreach ($pageLanguages as $pageLanguage) {
            $pageLanguageMap[$pageLanguage->page_id] = $pageLanguage;
        }

        return $pageLanguageMap;
    }

    /**
     * Get the default pageLanguage (homepage)
     *
     * @return PageLanguage
     */
    public function getDefault()
    {
        $defaultLanguageCode = $this->config->application->defaultLanguage;
        $defaultPageId = $this->config->application->defaultPage;

        return $this->getByPageId($defaultPageId, $defaultLanguageCode);
    }

    /**
     * @return PageLanguage|null
     */
    public function getNotFoundPage()
    {
        $defaultLanguageCode = $this->config->application->defaultLanguage;
        $pageId = $this->config->application->notFoundPage;

        return $this->getByPageId($pageId, $defaultLanguageCode);
    }
}