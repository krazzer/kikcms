<?php

namespace KikCMS\Services\Website;


use KikCMS\Classes\WebForm\WebForm;
use Phalcon\Http\Response;

class WebsiteService
{
    /**
     * Look inside the page's variables for form tags that need to be replaced.
     * This could mean a form is found that is send and needs to redirect. The redirect response will be returned if so.
     *
     * @param $variables
     * @return array|Response
     */
    public function getForms(array $variables)
    {
        foreach ($variables as $index => $variable){
            if( ! is_string($variable)){
                continue;
            }

            if( ! preg_match_all('/\{{ form\.([a-zA-Z0-9]+) }}/i', $variable, $matches)){
                continue;
            }

            $formClass = 'Website\\Forms\\' . $matches[1][0];

            if( ! class_exists($formClass)){
                continue;
            }

            /** @var WebForm $formObject */
            $formObject = new $formClass();
            $renderedForm = $formObject->render();

            if($renderedForm instanceof Response){
                return $renderedForm;
            }

            $variables[$index] = str_replace($matches[0][0], $renderedForm, $variable);
        }

        return $variables;
    }
}