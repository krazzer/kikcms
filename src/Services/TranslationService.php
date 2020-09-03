<?php declare(strict_types=1);

namespace KikCMS\Services;


use KikCMS\Classes\Translator;
use KikCmsCore\Services\DbService;
use KikCMS\Config\CacheConfig;
use KikCMS\Models\TranslationKey;
use KikCMS\Models\TranslationValue;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property DbService $dbService
 * @property CacheService $cacheService
 * @property Translator $translator
 */
class TranslationService extends Injectable
{
    /**
     * Creates a new TranslationKey and returns it's id
     *
     * @return int
     */
    public function createNewTranslationKeyId(): int
    {
        $translationKey = new TranslationKey();

        $translationKey->db = true;
        $translationKey->save();

        return (int) $translationKey->id;
    }

    /**
     * Add entries to the db for site-specific translation keys, that haven't been added yet
     */
    public function createSiteTranslationKeys()
    {
        $keys = array_keys($this->translator->getWebsiteTranslations());

        $query = (new Builder)
            ->columns([TranslationKey::FIELD_KEY])
            ->from(TranslationKey::class)
            ->inWhere(TranslationKey::FIELD_KEY, $keys);

        $presentKeys = $this->dbService->getValues($query);
        $missingKeys = array_diff($keys, $presentKeys);

        foreach ($missingKeys as $key) {
            $translationKey      = new TranslationKey();
            $translationKey->key = $key;

            $translationKey->save();
        }
    }

    /**
     * @param int $keyId
     * @param string $languageCode
     * @return null|string
     */
    public function getTranslationValue(int $keyId, string $languageCode): ?string
    {
        $cacheKey = $this->getValueCacheKey($languageCode, $keyId);

        return $this->cacheService->cache($cacheKey, function () use ($keyId, $languageCode) {
            $query = $this->getTranslationValueQuery($keyId, $languageCode);
            return $this->dbService->getValue($query);
        });
    }

    /**
     * @param int $translationKeyId
     * @param string $languageCode
     * @return Builder
     */
    public function getTranslationValueQuery(int $translationKeyId, string $languageCode): Builder
    {
        return (new Builder())
            ->from(TranslationValue::class)
            ->columns([TranslationValue::FIELD_VALUE])
            ->where('key_id = :keyId: AND language_code = :languageCode:', [
                'keyId'        => $translationKeyId,
                'languageCode' => $languageCode,
            ]);
    }

    /**
     * @param string $langCode
     * @return array
     */
    public function getUserTranslations(string $langCode): array
    {
        $query = (new Builder())
            ->columns(['tk.key', 'tv.value'])
            ->from(['tv' => TranslationValue::class])
            ->join(TranslationKey::class, 'tk.id = tv.key_id', 'tk')
            ->where('tk.key IS NOT NULL AND tv.language_code = :languageCode:', [
                'languageCode' => $langCode
            ]);

        return $this->dbService->getAssoc($query);
    }

    /**
     * @param string $languageCode
     * @param int|string $keyId
     * @return string
     */
    public function getValueCacheKey(string $languageCode, $keyId): string
    {
        return CacheConfig::TRANSLATION . CacheConfig::SEPARATOR . $languageCode . CacheConfig::SEPARATOR . $keyId;
    }

    /**
     * @param int $translationKeyId
     * @param string $languageCode
     * @return bool
     */
    public function valueExists(int $translationKeyId, string $languageCode): bool
    {
        $query = $this->getTranslationValueQuery($translationKeyId, $languageCode);
        return (bool) $query->getQuery()->execute()->count();
    }
}