<?php

namespace KikCMS\Classes;


use KikCMS\Config\KikCMSConfig;

class Translator
{
    /**
     * @param string $string
     * @param array $replaces
     *
     * @return string|array
     */
    public function tl(string $string, $replaces = [])
    {
        $translations = include(__DIR__ . '/../../../translations/nl.php');
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

        $translation = $translations;

        foreach ($replaces as $key => $replace) {
            if ( ! is_string($replace)) {
                continue;
            }

            $translation = str_replace(':' . $key, $replace, $translation);
        }

        return $translation;
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
}