<?php

namespace KikCMS\Classes;


use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\TranslationKey;
use KikCMS\Services\TranslationService;
use Phalcon\Di\Injectable;

/**
 * @property TranslationService $translationService
 */
class Translator extends Injectable
{
    private $languageCode = 'nl';

    /**
     * Backend translation, just checks the language files for the translation
     *
     * @param string $string
     * @param array $replaces
     * @return array|string
     */
    public function tlb(string $string, $replaces = [])
    {
        $translation = $this->getCmsTranslation($string);

        if(is_string($translation)){
            $translation = $this->replace($translation, $replaces);
        }

        return $translation;
    }

    /**
     * Frontend translation
     *
     * @param string|null $string
     * @param array $replaces
     * @param int|null $groupId
     *
     * @return mixed|string
     */
    public function tlf(string $string = null, $replaces = [], int $groupId = null)
    {
        if ( ! $string) {
            return '';
        }

        $translation = $this->getTranslationValue($string, $groupId);
        $translation = $this->replace($translation, $replaces);

        return $translation;
    }

    /**
     * @param string $string
     * @return string|array
     */
    public function getCmsTranslation(string $string)
    {
        $translations = include(__DIR__ . '/../../translations/nl.php');
        $stringParts  = explode('.', $string);

        if ( ! array_key_exists($stringParts[0], $translations)) {
            return null;
        }

        foreach ($stringParts as $part) {
            if (array_key_exists($part, $translations)) {
                $translations = $translations[$part];
            } else {
                $translations = null;
            }
        }

        if ( ! is_string($translations)) {
            return $translations;
        }

        return $translations;
    }

    /**
     * @return array
     */
    public function getContentTypeMap()
    {
        $contentTypeMap = [];

        foreach (KikCMSConfig::CONTENT_TYPES as $key => $typeId) {
            $contentTypeMap[$typeId] = $this->tlb('contentTypes.' . $key);
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
     * @param string $string
     * @param int|null $groupId
     * @return string
     */
    public function getKeyTranslation(string $string, int $groupId = null): string
    {
        $key = TranslationKey::findFirst([TranslationKey::FIELD_KEY . ' = ' . $this->db->escapeString($string)]);

        if ( ! $key) {
            $key = $this->createNewTranslationKey($string, $groupId);
        }

        return $this->getDbTranslation($key->id);
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
     * @param $key
     * @return string
     */
    private function getDbTranslation($key): string
    {
        return (string) $this->translationService->getTranslationValue($key, $this->getLanguageCode());
    }

    /**
     * @param string $string
     * @param int|null $groupId
     * @return string
     */
    private function getTranslationValue(string $string, int $groupId = null): string
    {
        // numeric values given indicate it's a translation managed from a DataTable
        if (is_numeric($string)) {
            return $this->getDbTranslation($string);
        }

        // see if there is a translation defined in the CMS translation files
        $cmsTranslation = (string) $this->getCmsTranslation($string);

        if ($cmsTranslation) {
            return $cmsTranslation;
        }

        // get the translation from the db
        $keyTranslation = $this->getKeyTranslation($string, $groupId);

        if ($keyTranslation) {
            return $keyTranslation;
        }

        return (string) $string;
    }

    /**
     * @param string $string
     * @param int|null $groupId
     *
     * @return TranslationKey
     */
    private function createNewTranslationKey(string $string, int $groupId = null)
    {
        $translationKey = new TranslationKey();

        $translationKey->key = $string;
        $translationKey->db  = 0;

        if ($groupId) {
            $translationKey->group_id = $groupId;
        }

        $translationKey->save();

        return $translationKey;
    }

    /**
     * @param string $translation
     * @param array $replaces
     * @return mixed|string
     */
    private function replace(string $translation, array $replaces)
    {
        foreach ($replaces as $key => $replace) {
            if ( ! is_string($replace)) {
                continue;
            }

            $translation = str_replace(':' . $key, $replace, $translation);
        }

        return $translation;
    }
}