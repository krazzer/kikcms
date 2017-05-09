<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Config\CacheConfig;
use KikCMS\Models\TranslationKey;
use KikCMS\Models\TranslationValue;
use KikCMS\Services\CacheService;

/**
 * @property CacheService $cacheService
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
            $this->addTextAreaField(TranslationValue::FIELD_VALUE, $language->name)
                ->table(TranslationValue::class, TranslationValue::FIELD_KEY_ID, false, [
                    TranslationValue::FIELD_LANGUAGE_CODE => $language->code
                ])
                ->setAttribute('data-language-code', $language->code);
        }
    }

    /**
     * @inheritdoc
     */
    protected function onSave()
    {
        $this->cacheService->clear(CacheConfig::USER_TRANSLATIONS);
    }
}