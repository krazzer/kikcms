<?php
declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;


use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\ControllerInterface;

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
    protected function view(string $view, array $parameters, int $statusCode = null): ResponseInterface
    {
        if($statusCode){
            $this->response->setStatusCode($statusCode);
        }

        return $this->response->setContent($this->view->getPartial($view, $parameters));
    }
}