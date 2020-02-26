<?php declare(strict_types=1);

namespace KikCMS\Models;

use KikCMS\Config\CacheConfig;
use KikCMS\Services\CacheService;
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
        $this->getCacheService()->clear(CacheConfig::TRANSLATION . ':' . $this->language_code . ':' . $this->key_id);
    }

    /**
     * @return CacheService
     */
    private function getCacheService(): CacheService
    {
        return $this->getDI()->get('cacheService');
    }
}