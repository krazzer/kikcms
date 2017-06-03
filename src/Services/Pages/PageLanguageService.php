<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
use KikCMS\Models\Field;
use KikCMS\Models\Page;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguage;
use KikCMS\Models\PageLanguageContent;
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
     * Get PageLanguages for the given pageId
     *
     * @param int $pageId
     * @return PageLanguage[]
     */
    public function getAllByPageId(int $pageId): array
    {
        $pageLanguages = [];

        $results = PageLanguage::find([
            'conditions' => 'page_id = :pageId:',
            'bind'       => ['pageId' => $pageId,],
        ]);

        foreach ($results as $result) {
            $pageLanguages[] = $result;
        }

        return $pageLanguages;
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

        if ( ! $pageMap) {
            return [];
        }

        $pageLanguages = PageLanguage::find([
            'conditions' => 'page_id IN ({ids:array}) AND language_code = :langCode:',
            'bind'       => ['ids' => array_keys($pageMap), 'langCode' => $languageCode]
        ]);

        return $this->toMap($pageLanguages);
    }

    /**
     * Get the default pageLanguage (homepage)
     *
     * @return PageLanguage
     */
    public function getDefault()
    {
        $defaultLanguageCode = $this->config->application->defaultLanguage;
        $defaultPageId       = $this->config->application->defaultPage;

        return $this->getByPageId($defaultPageId, $defaultLanguageCode);
    }

    /**
     * @return PageLanguage|null
     */
    public function getNotFoundPage()
    {
        $defaultLanguageCode = $this->config->application->defaultLanguage;
        $pageId              = $this->config->application->notFoundPage;

        return $this->getByPageId($pageId, $defaultLanguageCode);
    }

    /**
     * @param Page[] $pageMap
     * @param string $langCode
     * @param array $fields
     * @return array
     */
    public function getPageFieldTable(array $pageMap, string $langCode, $fields = []): array
    {
        $query = (new Builder)
            ->from(['p' => Page::class])
            ->leftJoin(PageContent::class, 'pc.page_id = p.id', 'pc')
            ->leftJoin(PageLanguageContent::class, 'plc.page_id = p.id AND plc.language_code = "' . $langCode . '"', 'plc')
            ->join(Field::class, 'pc.field_id = f.id OR plc.field_id = f.id', 'f')
            ->where('p.id IN ({ids:array})', ['ids' => array_keys($pageMap)])
            ->columns([
                'IFNULL(plc.page_id, pc.page_id) as pageId',
                'IFNULL(plc.value, pc.value) as value',
                Field::FIELD_VARIABLE,
            ]);

        if ($fields) {
            $query->andWhere('f.variable IN ({fields:array})', ['fields' => $fields]);
        }

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
     * @return PageLanguage[]
     */
    public function getPath(PageLanguage $pageLanguage): array
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
     * @return array
     */
    private function toMap(Resultset $results = null): array
    {
        if ( ! $results) {
            return [];
        }

        $pageLanguageMap = [];

        foreach ($results as $pageLanguage) {
            $pageLanguageMap[$pageLanguage->page_id] = $pageLanguage;
        }

        return $pageLanguageMap;
    }
}