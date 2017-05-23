<?php

namespace KikCMS\Services\Website;


use Exception;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Services\Pages\PageContentService;
use Phalcon\Http\Response;

/**
 * @property PageContentService
 */
class WebsiteService
{
    /**
     * Calls a method from the website, which may not exist
     *
     * @param $className
     * @param $methodName
     * @param array $arguments
     * @param bool $exceptionOnFail
     * @param bool $returnOnFail
     *
     * @return mixed
     * @throws Exception
     */
    public function callMethod(string $className, $methodName, array $arguments = [], $exceptionOnFail = false, $returnOnFail = false)
    {
        $className  = 'Website\\Classes\\' . $className;

        if ( ! class_exists($className)) {
            if($exceptionOnFail){
                throw new Exception('Class ' . $className . ' not found');
            } else {
                return $returnOnFail;
            }
        }

        $object = new $className();

        if ( ! method_exists($object, $methodName)) {
            if($exceptionOnFail) {
                throw new Exception('Method ' . $className . '::' . $methodName . ' not found');
            } else {
                return $returnOnFail;
            }
        }

        return call_user_func_array(array($object, $methodName), $arguments);
    }

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

    /**
     * @param string $templateFile
     * @return array
     */
    public function getWebsiteTemplateVariables(string $templateFile): array
    {
        $methodName = 'get' . ucfirst($templateFile) . 'Variables';

        return $this->callMethod('TemplateVariables', $methodName, [], false, []);
    }

    /**
     * @param array $variables
     * @return array
     */
    public function getWebsiteVariables(array $variables): array
    {
        return $this->callMethod('TemplateVariables', 'getVariables', [$variables], false, $variables);
    }
}