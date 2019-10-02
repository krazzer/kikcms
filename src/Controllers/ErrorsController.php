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
        return $this->getResponse('404');
    }

    /**
     * @param string $object
     * @return ResponseInterface
     */
    public function show404ObjectAction(string $object): ResponseInterface
    {
        $this->response->setStatusCode(404);

        return $this->getResponse('404object', [
            'object' => $object,
        ]);
    }

    /**
     * @return ResponseInterface
     */
    public function show401Action(): ResponseInterface
    {
        $this->response->setStatusCode(401);
        return $this->getResponse('401');
    }

    /**
     * @return ResponseInterface
     */
    public function show500Action(): ResponseInterface
    {
        $this->response->setStatusCode(500);
        return $this->getResponse('500');
    }

    /**
     * @param string $errorType
     * @param array $parameters
     * @return ResponseInterface
     */
    private function getResponse(string $errorType, array $parameters = []): ResponseInterface
    {
        if ($this->request->isAjax() && $this->config->isProd()) {
            return $this->response->setJsonContent([
                'title'       => $this->translator->tl('error.' . $errorType . '.title'),
                'description' => $this->translator->tl('error.' . $errorType . '.description', $parameters),
            ]);
        } else {
            return $this->response->setContent($this->view->getPartial('@kikcms/errors/show' . $errorType, $parameters));
        }
    }
}
