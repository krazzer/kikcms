<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
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
     * Get the PageLanguage by given pageId, using the default languageCode
     *
     * @param int $pageId
     * @param string|null $languageCode
     * @return PageLanguage|null
     */
    public function getByPageId(int $pageId, $languageCode = null)
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
}