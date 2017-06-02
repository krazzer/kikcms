<?php

namespace KikCMS\Classes;


use KikCMS\Config\CacheConfig;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\TranslationKey;
use KikCMS\Models\TranslationValue;
use KikCMS\Services\CacheService;
use KikCMS\Services\LanguageService;
use KikCMS\Services\TranslationService;
use Phalcon\Cache\Backend;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property TranslationService $translationService
 * @property DbService $dbService
 * @property CacheService $cacheService
 * @property Backend $cache
 * @property LanguageService $languageService
 */
class Translator extends Injectable
{
    private $languageCode = 'nl';

    /**
     * @param string|int|null $key
     * @param array $replaces
     *
     * @return string
     */
    public function tl($key, array $replaces = []): string
    {
        // no translation given = empty string
        if(empty($key)){
            return '';
        }

        // cache translation without using the cacheService shortcut for performance
        $cacheKey = CacheConfig::TRANSLATION . ':' . $this->getLanguageCode() . ':' . $key;

        if( ! $this->cache || ! $translation = $this->cache->get($cacheKey)) {
            // numeric values given indicate it's a translation managed from a DataTable
            if (is_numeric($key)) {
                return $this->getDbTranslation($key);
            }

            $translations = $this->getTranslations();

            if( ! array_key_exists($key, $translations)){
                throw new \InvalidArgumentException('Translation key "' . $key . '" does not exist');
            }

            $translation = $translations[$key];

            if($this->cache){
                $this->cache->save($cacheKey, $translation, CacheConfig::ONE_DAY);
            }
        }

        // replace string, not in a separate function for performance
        foreach ($replaces as $key => $replace) {
            if ( ! is_string($replace)) {
                continue;
            }

            $translation = str_replace(':' . $key, $replace, $translation);
        }

        return $translation;
    }

    /**
     * @param string $string
     * @return array
     */
    public function getCmsTranslationGroupKeys(string $string)
    {
        $translations = $this->getCmsTranslations();

        $group = [];

        foreach ($translations as $key => $value){
            if(substr($key, 0, strlen($string) + 1) === $string . '.'){
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
     * @return array
     */
    public function getTranslations(): array
    {
        $userTranslations    = $this->getUserTranslations();
        $websiteTranslations = $this->getWebsiteTranslations();
        $cmsTranslations     = $this->getCmsTranslations();

        return $userTranslations + $websiteTranslations + $cmsTranslations;
    }

    /**
     * @param mixed $languageCode
     * @return Translator
     */
    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;
        return $this;
    }

    /**
     * @return array
     */
    public function getWebsiteTranslations(): array
    {
        return $this->getByFile(SITE_PATH . 'resources/translations/' . $this->getLanguageCode() . '.php');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function translateDefaultLanguage(string $string): string
    {
        $currentLanguageCode = $this->getLanguageCode();

        $this->setLanguageCode($this->languageService->getDefaultLanguageCode());

        $translation = $this->tl($string);

        $this->setLanguageCode($currentLanguageCode);

        return $translation;

    }

    /**
     * @param $id
     * @return string
     */
    private function getDbTranslation(int $id): string
    {
        return (string) $this->translationService->getTranslationValue($id, $this->getLanguageCode());
    }

    /**
     * @param array $array
     * @param string $prefix
     * @return array
     */
    private function flatten(array $array, $prefix = ''): array
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
     * @return array [translationKey => value]
     */
    private function getUserTranslations(): array
    {
        $cacheKey = CacheConfig::USER_TRANSLATIONS . ':' . $this->getLanguageCode();

        return $this->cacheService->cache($cacheKey, function () {
            $query = (new Builder())
                ->columns(['tk.key', 'tv.value'])
                ->from(['tv' => TranslationValue::class])
                ->join(TranslationKey::class, 'tk.id = tv.key_id', 'tk')
                ->where('tk.key IS NOT NULL AND tv.language_code = :languageCode:', [
                    'languageCode' => $this->getLanguageCode()
                ]);

            return $this->dbService->getAssoc($query);
        });
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
     * @return array
     */
    private function getCmsTranslations(): array
    {
        return $this->getByFile(__DIR__ . '/../../resources/translations/' . $this->getLanguageCode() . '.php');
    }
}