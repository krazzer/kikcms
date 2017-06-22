<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
use KikCMS\Classes\Model\Model;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\Field;
use KikCMS\Models\Page;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguage;
use KikCMS\Models\PageLanguageContent;
use KikCMS\ObjectLists\PageLanguageMap;
use KikCMS\ObjectLists\PageMap;
use KikCMS\Services\LanguageService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Resultset;

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
     * @param string $pageKey
     * @param string $languageCode
     * @return Model|PageLanguage|null
     */
    public function getByPageKey(string $pageKey, string $languageCode)
    {
        $query = (new Builder())
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'pl.page_id = p.id', 'p')
            ->where('p.key = :pageKey: AND pl.language_code = :langCode:', [
                'pageKey'  => $pageKey,
                'langCode' => $languageCode
            ]);

        return $this->dbService->getObject($query);
    }

    /**
     * Get PageLanguages for the given pageId
     *
     * @param int $pageId
     * @return PageLanguageMap
     */
    public function getAllByPageId(int $pageId): PageLanguageMap
    {
        $results = PageLanguage::find([
            'conditions' => 'page_id = :pageId:',
            'bind'       => ['pageId' => $pageId],
        ]);

        return $this->toMap($results);
    }

    /**
     * @param PageMap $pageMap
     * @param string $languageCode
     * @return PageLanguageMap
     */
    public function getByPageMap(PageMap $pageMap, string $languageCode = null): PageLanguageMap
    {
        if ( ! $languageCode) {
            $languageCode = $this->languageService->getDefaultLanguageCode();
        }

        if ($pageMap->isEmpty()) {
            return new PageLanguageMap();
        }

        $pageLanguages = PageLanguage::find([
            'conditions' => 'page_id IN ({ids:array}) AND language_code = :langCode:',
            'bind'       => ['ids' => $pageMap->keys(), 'langCode' => $languageCode]
        ]);

        return $this->toMap($pageLanguages);
    }

    /**
     * Get the default pageLanguage (homepage), in the default language
     *
     * @return PageLanguage
     */
    public function getDefault()
    {
        $defaultLanguageCode = $this->config->application->defaultLanguage;
        return $this->getByPageKey(KikCMSConfig::KEY_PAGE_DEFAULT, $defaultLanguageCode);
    }

    /**
     * @param string|null $languageCode
     * @return PageLanguage|null
     */
    public function getNotFoundPage(string $languageCode = null)
    {
        $languageCode = $languageCode ?: $this->config->application->defaultLanguage;
        return $this->getByPageKey(KikCMSConfig::KEY_PAGE_NOT_FOUND, $languageCode);
    }

    /**
     * @param PageMap $pageMap
     * @param string $langCode
     * @return array
     */
    public function getPageFieldTable(PageMap $pageMap, string $langCode): array
    {
        if ($pageMap->isEmpty()) {
            return [];
        }

        $query = (new Builder)
            ->from(['p' => Page::class])
            ->leftJoin(PageContent::class, 'pc.page_id = p.id', 'pc')
            ->leftJoin(PageLanguageContent::class, 'plc.page_id = p.id AND plc.language_code = "' . $langCode . '"', 'plc')
            ->join(Field::class, 'pc.field_id = f.id OR plc.field_id = f.id', 'f')
            ->where('p.id IN ({ids:array})', ['ids' => $pageMap->keys()])
            ->columns([
                'IFNULL(plc.page_id, pc.page_id) as pageId',
                'IFNULL(plc.value, pc.value) as value',
                Field::FIELD_VARIABLE,
            ]);

        $rows  = $this->dbService->getRows($query);
        $table = [];

        foreach ($rows as $row) {
            $pageId   = $row['pageId'];
            $value    = $row['value'];
            $variable = $row[Field::FIELD_VARIABLE];

            if ( ! array_key_exists($pageId, $table)) {
                $table[$pageId] = [];
            }

            $table[$pageId][$variable] = $value;
        }

        return $table;
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return PageLanguageMap
     */
    public function getPath(PageLanguage $pageLanguage): PageLanguageMap
    {
        $query = (new Builder)
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'p.id = pl.page_id', 'p')
            ->where('p.lft <= :lft: AND p.rgt >= :rgt: AND p.type != "menu"', [
                'lft' => $pageLanguage->page->lft,
                'rgt' => $pageLanguage->page->rgt,
            ])->orderBy('lft ASC');

        return $this->toMap($query->getQuery()->execute());
    }

    /**
     * @param Resultset|null $results
     * @return PageLanguageMap
     */
    private function toMap(Resultset $results = null): PageLanguageMap
    {
        $pageLanguageMap = new PageLanguageMap();

        if ( ! $results) {
            return $pageLanguageMap;
        }

        foreach ($results as $pageLanguage) {
            $pageLanguageMap->add($pageLanguage, $pageLanguage->page_id);
        }

        return $pageLanguageMap;
    }
}