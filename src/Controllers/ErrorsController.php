<?php declare(strict_types=1);

namespace KikCMS\Controllers;


use Phalcon\Http\ResponseInterface;

class ErrorsController extends BaseCmsController
{
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->view->reset();
        $this->view->hideMenu = $this->request->isAjax();
    }

    /**
     * @return ResponseInterface
     */
    public function show404Action(): ResponseInterface
    {
        $this->response->setStatusCode(404);
        return $this->errorService->getResponse('404');
    }

    /**
     * @param string|null $object
     * @return ResponseInterface
     */
    public function show404ObjectAction(?string $object): ResponseInterface
    {
        $this->response->setStatusCode(404);

        return $this->errorService->getResponse('404object', [
            'object' => $object,
        ]);
    }

    /**
     * @return ResponseInterface
     */
    public function show401Action(): ResponseInterface
    {
        $this->response->setStatusCode(401);
        return $this->errorService->getResponse('401');
    }

    /**
     * @param $error
     * @return ResponseInterface
     */
    public function show500Action($error = null): ResponseInterface
    {
        $this->response->setStatusCode(500);
        return $this->errorService->getResponse('500', ['error' => $this->config->isProd() ? null : $error]);
    }
}
