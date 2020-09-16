<?php declare(strict_types=1);

namespace KikCMS\Services;


use KikCMS\Classes\Phalcon\IniConfig;
use KikCMS\ObjectLists\LanguageMap;
use KikCmsCore\Services\DbService;
use KikCMS\Config\CacheConfig;
use KikCMS\Models\Language;
use KikCMS\Classes\Phalcon\Injectable;

/**
 * Service for managing different languages for the website, and also for configuring these in the CMS
 *
 * @property IniConfig $config
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
     * @return string
     */
    public function getDefaultCmsLanguageCode(): string
    {
        if (isset($this->config->application->defaultCmsLanguage)) {
            return $this->config->application->defaultCmsLanguage;
        }

        return $this->getDefaultLanguageCode();
    }

    /**
     * @param bool $activeOnly
     * @return LanguageMap
     */
    public function getLanguages(bool $activeOnly = false): LanguageMap
    {
        $languages = $this->cacheService->cache(CacheConfig::LANGUAGES, function () use ($activeOnly){
            return $this->dbService->toMap(Language::find(), Language::FIELD_CODE);
        });

        $languageMap = new LanguageMap($languages);

        if ($activeOnly) {
            foreach ($languageMap as $code => $language) {
                if ( ! $language->active) {
                    $languageMap->remove($code);
                }
            }
        }

        return $languageMap;
    }

    /**
     * @return string
     */
    public function getDefaultLanguageName(): string
    {
        foreach ($this->getLanguages() as $language){
            if($language->code == $this->getDefaultLanguageCode()){
                return (string) $language->name;
            }
        }

        return '';
    }
}