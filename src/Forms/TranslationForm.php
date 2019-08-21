<?php declare(strict_types=1);

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Config\CacheConfig;
use KikCMS\Models\Language;
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

        foreach ($this->languageService->getLanguages() as $language) {
            $relationKey = 'value' . ucfirst($language->code) . ':value';
            $valueField  = $this->addTextAreaField($relationKey, (string) $language->name)
                ->setAttribute('data-language-code', $language->code);

            if ($key = $this->getObject()) {
                $valueField->setDefault($this->cache->get($this->getCacheKey($language)));
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function onSave()
    {
        // clear cache
        foreach ($this->languageService->getLanguages() as $language) {
            $this->cacheService->clear($this->getCacheKey($language));
        }

        $this->cacheService->clear(CacheConfig::USER_TRANSLATIONS);
    }

    /**
     * @param Language $language
     * @return string
     */
    private function getCacheKey(Language $language): string
    {
        return CacheConfig::TRANSLATION . ':' . $language->code . ':' . $this->getObject()->key;
    }
}