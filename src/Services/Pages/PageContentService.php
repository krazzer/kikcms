<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
use KikCMS\Models\Field;
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
        $query = (new Builder())
            ->from(['pc' => PageContent::class])
            ->join(Field::class, 'pc.field_id = f.id AND f.multilingual = 0', 'f')
            ->where('page_id = :pageId:', ['pageId' => $pageLanguage->page_id])
            ->columns(['f.variable', 'pc.value']);

        $queryMultiLingual = (new Builder())
            ->from(['plc' => PageLanguageContent::class])
            ->join(Field::class, 'plc.field_id = f.id AND f.multilingual = 1', 'f')
            ->where('page_id = :pageId: AND language_code = :languageCode:', [
                'pageId' => $pageLanguage->page_id, 'languageCode' => $pageLanguage->language_code
            ])
            ->columns(['f.variable', 'plc.value']);

        return $this->dbService->getAssoc($query) + $this->dbService->getAssoc($queryMultiLingual);
    }
}