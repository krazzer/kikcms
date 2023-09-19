<?php declare(strict_types=1);

namespace KikCMS\Models;

use KikCmsCore\Classes\Model;

class TranslationKey extends Model
{
    const TABLE = 'cms_translation_key';
    const ALIAS = 'tk';

    const FIELD_ID  = 'id';
    const FIELD_KEY = 'key';

    /**
     * Initialize relations
     */
    public function initialize(): void
    {
        parent::initialize();

        $languages = $this->getDI()->get('languageService')->getLanguages();

        // add an relation for each language, e.g. valueEn
        foreach ($languages as $language) {
            $this->hasOne(self::FIELD_ID, TranslationValue::class, TranslationValue::FIELD_KEY_ID, [
                'alias'    => 'value' . ucfirst($language->code),
                'defaults' => [TranslationValue::FIELD_LANGUAGE_CODE => $language->code],
            ]);
        }
    }
}