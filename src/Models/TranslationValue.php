<?php declare(strict_types=1);

namespace KikCMS\Models;

use KikCMS\Services\CacheService;
use KikCMS\Services\TranslationService;
use KikCmsCore\Classes\Model;

class TranslationValue extends Model
{
    const TABLE = 'cms_translation_value';

    const FIELD_KEY_ID        = 'key_id';
    const FIELD_LANGUAGE_CODE = 'language_code';
    const FIELD_VALUE         = 'value';

    /**
     * Remove cache when updating a value
     */
    public function afterUpdate()
    {
        $this->getCacheService()->clear($this->getCacheKey());
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return $this->getTranslationService()->getValueCacheKey((string) $this->language_code, $this->key_id);
    }

    /**
     * @return CacheService
     */
    private function getCacheService(): CacheService
    {
        return $this->getDI()->get('cacheService');
    }

    /**
     * @return TranslationService
     */
    private function getTranslationService(): TranslationService
    {
        return $this->getDI()->get('translationService');
    }
}