<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Translator;
use KikCMS\Config\CacheConfig;
use KikCMS\Forms\TranslationForm;
use KikCMS\Models\TranslationKey;
use KikCMS\Models\TranslationValue;
use KikCMS\Services\CacheService;
use KikCMS\Services\Util\StringService;

/**
 * @property CacheService $cacheService
 * @property StringService $stringService
 * @property Translator $translator
 */
class Translations extends DataTable
{
    protected $jsClass = 'TranslationsDataTable';

    /**
     * @inheritdoc
     */
    public function delete(array $ids)
    {
        parent::delete($ids);

        $this->cacheService->clear(CacheConfig::USER_TRANSLATIONS);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultQuery()
    {
        return parent::getDefaultQuery()
            ->leftJoin(TranslationValue::class, 'tv.key_id = tk.id', 'tv')
            ->where('tk.db = 0 AND (tv.language_code IS NULL OR tv.language_code = :languageCode:)', [
                'languageCode' => $this->languageService->getDefaultLanguageCode()
            ])->columns(['tk.id', 'tk.key', 'tv.value'])
            ->orderBy('tk.key');
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return [
            $this->translator->tl('dataTables.translation.singular'),
            $this->translator->tl('dataTables.translation.plural'),
        ];
    }

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
    public function getFormClass(): string
    {
        return TranslationForm::class;
    }

    /**
     * @inheritdoc
     */
    protected function getTableFieldMap(): array
    {
        return [
            'id'    => $this->translator->tl('fields.id'),
            'key'   => $this->translator->tl('fields.key'),
            'value' => $this->languageService->getDefaultLanguageName(),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->setFieldFormatting('value', function ($value, $row) {
            if( ! $value){
                $value = $this->translator->tl($row['key'], [], $this->languageService->getDefaultLanguageCode());
            }

            return $this->stringService->truncate($value);
        });
    }
}