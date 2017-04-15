<?php

namespace KikCMS\Classes;


use KikCMS\Config\KikCMSConfig;
use KikCMS\Services\TranslationService;
use Phalcon\Di\Injectable;

/**
 * @property TranslationService $translationService
 */
class Translator extends Injectable
{
    private $languageCode;

    /**
     * @param string $string
     * @param array $replaces
     * @return string|array
     */
    public function tl(string $string = null, $replaces = [])
    {
        if( ! $string){
            return '';
        }

        $translation = $this->getTranslationValue($string);

        foreach ($replaces as $key => $replace) {
            if ( ! is_string($replace)) {
                continue;
            }

            $translation = str_replace(':' . $key, $replace, $translation);
        }

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

        if( ! array_key_exists($stringParts[0], $translations)){
            return null;
        }

        foreach ($stringParts as $part) {
            if (array_key_exists($part, $translations)) {
                $translations = $translations[$part];
            } else {
                $translations = null;
            }
        }

        if (!is_string($translations)) {
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
            $contentTypeMap[$typeId] = $this->tl('contentTypes.' . $key);
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
     * @return string|null
     */
    private function getDbTranslation($key)
    {
        return $this->translationService->getTranslationValue($key, $this->getLanguageCode());
    }

    /**
     * @param string $string
     * @return string
     */
    private function getTranslationValue(string $string)
    {
        if(is_numeric($string)){
            return $this->getDbTranslation($string);
        }

        return $this->getCmsTranslation($string);
    }
}