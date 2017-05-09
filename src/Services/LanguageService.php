<?php

namespace KikCMS\Services;


use KikCMS\Classes\DbService;
use KikCMS\Config\CacheConfig;
use KikCMS\Models\Language;
use Phalcon\Config;
use Phalcon\Di\Injectable;

/**
 * Service for managing different languages for the website, and also for configuring these in the CMS
 *
 * @property Config $config
 * @property CacheService $cacheService
 * @property DbService $dbService
 */
class LanguageService extends Injectable
{
    /**
     * @return string
     */
    public function getDefaultLanguageCode(): string
    {
        return $this->config->application->defaultLanguage;
    }

    /**
     * @param bool $activeOnly
     * @return Language[]
     */
    public function getLanguages(bool $activeOnly = false)
    {
        return $this->cacheService->cache(CacheConfig::LANGUAGES, function () use ($activeOnly){
            if ($activeOnly) {
                $results = Language::find([Language::FIELD_ACTIVE . ' = 1']);
            } else {
                $results = Language::find();
            }

            return $this->dbService->toMap($results, Language::FIELD_CODE);
        });
    }
}