<?php

namespace KikCMS\Classes;


class Translator
{
    /**
     * @param string $string
     * @param array $replaces
     *
     * @return string
     */
    public function tl(string $string, $replaces = []): string
    {
        $translations = include(__DIR__ . '/../../../translations/nl.php');
        $stringParts  = explode('.', strtolower($string));

        foreach ($stringParts as $part) {
            if (array_key_exists($part, $translations)) {
                $translations = $translations[$part];
            } else {
                break;
            }
        }

        if (!is_string($translations)) {
            return '';
        }

        $translation = $translations;

        foreach ($replaces as $key => $replace) {
            $translation = str_replace(':' . $key, $replace, $translation);
        }

        return $translation;
    }
}