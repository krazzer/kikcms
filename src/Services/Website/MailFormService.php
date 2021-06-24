<?php


namespace KikCMS\Services\Website;


use KikCMS\Classes\Phalcon\Injectable;

class MailFormService extends Injectable
{
    /**
     * @param array $input
     * @return string
     */
    public function getHtml(array $input): string
    {
        $contents = '';

        foreach ($input as $label => $value){
            $contents .= '<b>' . $label . ':</b><br>';
            $contents .= $value . '<br><br>';
        }

        return $contents;
    }
}