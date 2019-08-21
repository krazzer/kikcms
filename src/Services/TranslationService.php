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
     * @param int $translationKeyId
     * @param string $languageCode
     * @return null|string
     */
    public function getTranslationValue(int $translationKeyId, string $languageCode): ?string
    {
        $cacheKey = CacheConfig::TRANSLATION . ':' . $languageCode . ':' . $translationKeyId;

        return $this->cacheService->cache($cacheKey, function() use ($translationKeyId, $languageCode){
            $query = $this->getTranslationValueQuery($translationKeyId, $languageCode);
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
     * @param int $translationKeyId
     * @param string $languageCode
     * @return bool
     */
    public function valueExists(int $translationKeyId, string $languageCode): bool
    {
        $query = $this->getTranslationValueQuery($translationKeyId, $languageCode);
        return (bool) $query->getQuery()->execute()->count();
    }

    /**
     * @param $value
     * @param int $translationKeyId
     * @param string $languageCode
     */
    public function saveValue($value, int $translationKeyId, string $languageCode)
    {
        if ($this->valueExists($translationKeyId, $languageCode)) {
            $this->dbService->update(TranslationValue::class, [
                TranslationValue::FIELD_VALUE => $value
            ], [
                TranslationValue::FIELD_LANGUAGE_CODE => $languageCode,
                TranslationValue::FIELD_KEY_ID        => $translationKeyId,
            ]);
        } else {
            $this->dbService->insert(TranslationValue::class, [
                TranslationValue::FIELD_VALUE         => $value,
                TranslationValue::FIELD_LANGUAGE_CODE => $languageCode,
                TranslationValue::FIELD_KEY_ID        => $translationKeyId,
            ]);
        }

        $this->cacheService->clear(CacheConfig::TRANSLATION . ':' . $languageCode . ':' . $translationKeyId);
    }

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

        foreach ($missingKeys as $key){
            $translationKey = new TranslationKey();
            $translationKey->key = $key;

            $translationKey->save();
        }
    }
}