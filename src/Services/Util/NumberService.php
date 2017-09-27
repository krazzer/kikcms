<?php

namespace KikCMS\Services\Util;


use KikCMS\Classes\Translator;
use Phalcon\Di\Injectable;

/**
 * Utility Service for handling numbers
 *
 * @property Translator $translator
 */
class NumberService extends Injectable
{
    /**
     * Convert a float to a formatted price string
     *
     * @param float $amount
     * @return string
     */
    public function getPriceFormat(float $amount): string
    {
        $decimalNotation = $this->translator->tl('system.decimalNotation');

        if($decimalNotation == 'point'){
            return number_format($amount, 2, '.', ',');
        } else {
            return number_format($amount, 2, ',', '.');
        }
    }
}