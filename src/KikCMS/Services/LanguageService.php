<?php

namespace KikCMS\Services;


use KikCMS\Config\CacheConfig;
use KikCMS\Models\Language;
use Phalcon\Cache\Backend;
use Phalcon\Config;
use Phalcon\Di\Injectable;

/**
 * Service for managing different languages for the website, and also for configuring these in the CMS
 *
 * @property Config $config
 * @property Backend $cache
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
        if ($languages = $this->cache->exists(CacheConfig::LANGUAGES)) {
            return $this->cache->get(CacheConfig::LANGUAGES);
        }

        /** @var Language[] $languages */
        if ($activeOnly) {
            $languages = Language::find([Language::FIELD_ACTIVE . ' = 1']);
        } else {
            $languages = Language::find();
        }

        $this->cache->save(CacheConfig::LANGUAGES, $languages, CacheConfig::ONE_DAY);

        return $languages;
    }
}