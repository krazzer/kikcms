<?php
declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;


use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\ControllerInterface;
use Website\Classes\TemplateVariables;

/**
 * @property TemplateVariables $templateVariables
 */
abstract class Controller extends Injectable implements ControllerInterface
{
    /**
     * Phalcon\Mvc\Controller constructor
     */
    public final function __construct() {}

    /**
     * @param string $view
     * @param array $parameters
     * @param int|null $statusCode
     * @return ResponseInterface
     */
    protected function view(string $view, array $parameters = [], int $statusCode = null): ResponseInterface
    {
        if($statusCode){
            $this->response->setStatusCode($statusCode);
        }

        return $this->response->setContent($this->view->getPartial($view, $parameters));
    }

    /**
     * @param string $view
     * @param array $parameters
     * @param int|null $statusCode
     * @return ResponseInterface
     */
    protected function frontendView(string $view, array $parameters, int $statusCode = null): ResponseInterface
    {
        $globalParameters = $this->templateVariables->getGlobalVariables();

        $globalParameters['helper'] = $this->frontendHelper;

        return $this->view($view, $parameters + $globalParameters, $statusCode);
    }
}