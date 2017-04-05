<?php

namespace KikCMS\Services;


use KikCMS\Models\Language;
use Phalcon\Config;
use Phalcon\Di\Injectable;

/**
 * Service for managing different languages for the website, and also for configuring these in the CMS
 *
 * @property Config $config
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
        /** @var Language[] $languages */
        if ($activeOnly) {
            $languages = Language::find([Language::FIELD_ACTIVE . ' = 1']);
        } else {
            $languages = Language::find();
        }

        return $languages;
    }
}