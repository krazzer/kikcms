<?php declare(strict_types=1);

namespace KikCMS\Classes;


use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\CacheConfig;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\TranslationKey;
use KikCMS\Models\TranslationValue;
use Monolog\Logger;
use Phalcon\Mvc\Model\Query\Builder;

class Translator extends Injectable
{
    /** @var null|string */
    private $languageCode = null;

    /** @var array */
    private $siteFiles = [];

    /** @var array */
    private $cmsFiles = [];

    /**
     * @param array $siteFiles
     * @param array $cmsFiles
     */
    public function __construct(array $cmsFiles = [], array $siteFiles = [])
    {
        $this->siteFiles = $siteFiles;
        $this->cmsFiles  = $cmsFiles;
    }

    /**
     * @param array $array
     * @param string $prefix
     * @return array
     */
    public function flatten(array $array, $prefix = ''): array
    {
        $result = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + $this->flatten($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param string|int|null $key
     * @param array $replaces
     * @param string|null $langCode if null, this->getLanguageCode() will be used
     * @return string
     */
    public function tl($key, array $replaces = [], $langCode = null): string
    {
        $langCode = $langCode ?: $this->getLanguageCode();

        // no translation given = empty string
        if (empty($key)) {
            return '';
        }

        // cache translation without using the cacheService shortcut for performance
        $cacheKey = CacheConfig::TRANSLATION . ':' . $langCode . ':' . $key;

        if ( ! $this->cache || ! $translation = $this->cache->get($cacheKey)) {
            // numeric values given indicate it's a translation managed from a DataTable
            if (is_numeric($key)) {
                return $this->getDbTranslation((int) $key, $langCode);
            }

            $translations = $this->getTranslations($langCode);

            // if translation is not found, log the error, and return key instead
            if ( ! array_key_exists($key, $translations)) {
                $this->logger->log(Logger::NOTICE, 'Translation key "' . $key . '" does not exist');
                return $key;
            }

            $translation = $translations[$key];

            if ($this->cache) {
                $this->cache->save($cacheKey, $translation, CacheConfig::ONE_DAY);
            }
        }

        // replace string, not in a separate function for performance
        foreach ($replaces as $key => $replace) {
            if ( ! is_string($replace) && ! is_numeric($replace)) {
                continue;
            }

            $translation = str_replace(':' . $key, $replace, $translation);
        }

        return (string) $translation;
    }

    /**
     * @param string $string
     * @return array
     */
    public function getCmsTranslationGroupKeys(string $string)
    {
        $translations = $this->getCmsTranslations();

        $group = [];

        foreach ($translations as $key => $value) {
            if (substr($key, 0, strlen($string) + 1) === $string . '.') {
                $group[] = $key;
            }
        }

        return $group;
    }

    /**
     * @return array
     */
    public function getContentTypeMap()
    {
        $contentTypeMap = [];

        foreach (KikCMSConfig::CONTENT_TYPES as $key => $typeId) {
            $contentTypeMap[$typeId] = $this->tl('contentTypes.' . $key);
        }

        return $contentTypeMap;
    }

    /**
     * @return mixed
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * @param string|null $langCode
     * @return array
     */
    public function getTranslations(string $langCode = null): array
    {
        $langCode = $langCode ?: $this->getLanguageCode();

        $userTranslations    = $this->getUserTranslations($langCode);
        $websiteTranslations = $this->getWebsiteTranslations($langCode);
        $pluginTranslations  = $this->getPluginTranslations($langCode);
        $cmsTranslations     = $this->getCmsTranslations($langCode);

        return $userTranslations + $websiteTranslations + $pluginTranslations + $cmsTranslations;
    }

    /**
     * @param mixed $languageCode
     * @return Translator
     */
    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;

        $this->setValidatorMessages();

        return $this;
    }

    /**
     * @param string|null $langCode
     * @return array [translationKey => value]
     */
    public function getUserTranslations(string $langCode = null): array
    {
        $langCode = $langCode ?: $this->getLanguageCode();
        $cacheKey = CacheConfig::USER_TRANSLATIONS . ':' . $langCode;

        return $this->cacheService->cache($cacheKey, function () use ($langCode) {
            $query = (new Builder())
                ->columns(['tk.key', 'tv.value'])
                ->from(['tv' => TranslationValue::class])
                ->join(TranslationKey::class, 'tk.id = tv.key_id', 'tk')
                ->where('tk.key IS NOT NULL AND tv.language_code = :languageCode:', [
                    'languageCode' => $langCode
                ]);

            return $this->dbService->getAssoc($query);
        });
    }

    /**
     * @param string|null $langCode
     * @return array
     */
    public function getWebsiteTranslations(string $langCode = null): array
    {
        $langCode = $langCode ?: $this->getLanguageCode();

        if ( ! array_key_exists($langCode, $this->siteFiles)) {
            return [];
        }

        return $this->getByFile($this->siteFiles[$langCode]);
    }

    /**
     * @param string $string
     * @param array $replaces
     * @return string
     */
    public function translateDefaultLanguage(string $string, array $replaces = []): string
    {
        return $this->tl($string, $replaces, $this->languageService->getDefaultLanguageCode());
    }

    /**
     * @param int $id
     * @param string|null $langCode
     * @return string
     */
    private function getDbTranslation(int $id, string $langCode = null): string
    {
        $langCode = $langCode ?: $this->getLanguageCode();

        return (string) $this->translationService->getTranslationValue($id, $langCode);
    }

    /**
     * @param string $file
     * @return array
     */
    private function getByFile(string $file): array
    {
        if ( ! file_exists($file)) {
            return [];
        }

        return $this->flatten(include $file);
    }

    /**
     * @param string|null $langCode
     * @return array
     */
    private function getCmsTranslations(string $langCode = null): array
    {
        $langCode = $langCode ?: $this->getLanguageCode();

        if ( ! array_key_exists($langCode, $this->cmsFiles)) {
            return [];
        }

        return $this->getByFile($this->cmsFiles[$langCode]);
    }

    /**
     * @param string|null $langCode
     * @return array
     */
    private function getPluginTranslations(string $langCode = null): array
    {
        $langCode = $langCode ?: $this->getLanguageCode();

        $translations = [];

        $pluginsList = $this->websiteSettings->getPluginList();

        foreach ($pluginsList as $plugin) {
            $translationsFile = $plugin->getTranslationsDirectory() . $langCode . '.php';

            if (file_exists($translationsFile)) {
                $translations += $this->getByFile($translationsFile);
            }
        }

        return $translations;
    }

    /**
     * Set the validators' default messages to match the current language
     */
    private function setValidatorMessages()
    {
        $webFormMessagesKeys = $this->getCmsTranslationGroupKeys('webform.messages');

        $defaultMessages = [];

        foreach ($webFormMessagesKeys as $key) {
            $defaultMessages[last(explode('.', $key))] = $this->tl($key);
        }

        $this->validation->setDefaultMessages($defaultMessages);
    }
}