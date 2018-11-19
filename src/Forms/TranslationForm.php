<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Config\CacheConfig;
use KikCMS\Models\TranslationKey;
use KikCMS\Services\CacheService;
use KikCMS\Services\LanguageService;

/**
 * @property CacheService $cacheService
 * @property LanguageService $languageService
 */
class TranslationForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return TranslationKey::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $translationKeys = array_keys($this->translator->getWebsiteTranslations());

        sort($translationKeys);

        $keyOptions = array_combine($translationKeys, $translationKeys);
        $keyLabel   = $this->translator->tl('fields.key');

        $this->addSelectField(TranslationKey::FIELD_KEY, $keyLabel, $keyOptions);

        foreach ($this->languageService->getLanguages() as $language){
            $relationKey = 'value' . ucfirst($language->code) . ':value';
            $this->addTextAreaField($relationKey, $language->name)->setAttribute('data-language-code', $language->code);
        }
    }

    /**
     * @inheritdoc
     */
    protected function onSave()
    {
        // clear cache
        foreach ($this->languageService->getLanguages() as $language){
            $cacheKey = CacheConfig::TRANSLATION . ':' . $language->code . ':' . $this->getObject()->key;
            $this->cacheService->clear($cacheKey);
        }

        $this->cacheService->clear(CacheConfig::USER_TRANSLATIONS);
    }
}