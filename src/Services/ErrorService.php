<?php
declare(strict_types=1);

namespace KikCMS\Services;


use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Http\ResponseInterface;
use stdClass;

class ErrorService extends Injectable
{
    /**
     * @param $error
     * @param bool $isProduction
     * @return string|null
     */
    public function getErrorView($error, bool $isProduction): ?string
    {
        if ( ! $error) {
            return null;
        }

        $isRecoverableError = $this->isRecoverableError($error);

        // don't show recoverable errors in production
        if ($isProduction && $isRecoverableError) {
            return null;
        }

        http_response_code(500);

        if ($this->request->isAjax() && ! $isProduction) {
            return '500content';
        }

        return '500';
    }

    /**
     * @param string $errorType
     * @param array $parameters
     * @return ResponseInterface
     */
    public function getResponse(string $errorType, array $parameters = []): ResponseInterface
    {
        $title       = $this->translator->tl('error.' . $errorType . '.title');
        $description = $this->translator->tl('error.' . $errorType . '.description', $parameters);

        if ($this->request->isAjax() && $this->config->isProd()) {
            return $this->response->setJsonContent([
                'title'       => $this->translator->tl('error.' . $errorType . '.title'),
                'description' => $this->translator->tl('error.' . $errorType . '.description', $parameters),
            ]);
        } else {
            if($this->config->isProd()){
                return $this->frontendService->getMessageResponse($title, $description);
            } else {
                $content = $this->view->getPartial('@kikcms/errors/show' . $errorType, $parameters);
                return $this->response->setContent($content);
            }
        }
    }

    /**
     * @param mixed $error
     */
    public function handleError($error)
    {
        $isProduction = $this->config->isProd();

        if ( ! $errorView = $this->getErrorView($error, $isProduction)) {
            return;
        }

        echo $this->getResponse($errorView, ['error' => $isProduction ? null : $error])->getContent();
    }

    /**
     * @param stdClass|array $error
     * @return bool
     */
    public function isRecoverableError($error): bool
    {
        if ( ! $errorType = $this->getErrorType($error)) {
            return false;
        }

        return ! in_array($errorType, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]);
    }

    /**
     * @param stdClass|array $error
     * @return null|int
     */
    private function getErrorType($error): ?int
    {
        if (is_object($error)) {
            return $error->type ?? null;
        }

        return $error['type'] ?? null;
    }
}