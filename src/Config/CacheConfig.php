<?php

namespace KikCMS\Config;

/**
 * Contains various constants for globally used cache keys
 */
class CacheConfig
{
    const ONE_HOUR = 3600;
    const ONE_DAY  = self::ONE_HOUR * 24;
    const ONE_YEAR = self::ONE_DAY * 365;

    const LANGUAGES         = 'languages';
    const TRANSLATION       = 'translation';
    const USER_TRANSLATIONS = 'userTranslations';

    const PAGE_LANGUAGE_FOR_URL = 'pageLanguageForUrl';
    const URL                   = 'url';
    const MENU                  = 'menu';
    const MENU_PAGES            = 'menuPages';

    const STATS_REQUIRE_UPDATE     = 'statsRequireUpdate';
    const STATS_UPDATE_IN_PROGRESS = 'statsUpdateInProgress';

    const SEPARATOR = ':';
}