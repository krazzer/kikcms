<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
use KikCMS\Models\Field;
use KikCMS\Models\PageContent;
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
            ->join(Field::class, 'pc.field_id = f.id', 'f')
            ->where('page_id = :pageId: AND language_code = :languageCode:', [
                'pageId' => $pageLanguage->page_id, 'languageCode' => $pageLanguage->language_code
            ])
            ->columns(['f.variable', 'pc.value']);

        return $this->dbService->getAssoc($query);
    }
}