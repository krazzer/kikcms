<?php
declare(strict_types=1);

namespace KikCMS\Services;


use KikCMS\Config\KikCMSConfig;
use Phalcon\Config;
use Phalcon\Di\Injectable;

/**
 * @property Config config
 */
class ErrorService extends Injectable
{
    /**
     * @param mixed $error
     */
    public function handleError($error)
    {
        if ( ! $error) {
            return;
        }

        $isProduction       = $this->config->application->env === KikCMSConfig::ENV_PROD;
        $isRecoverableError = $this->isRecoverableError($error);

        // don't show recoverable errors in production
        if ($isProduction && $isRecoverableError) {
            return;
        }

        http_response_code(500);

        if ($this->isAjaxRequest() && ! $isProduction) {
            echo $this->view->getRender('errors', 'error500content', ['error' => $error]);
            return;
        }

        echo $this->view->getRender('errors', 'show500', [
            'error' => $isProduction ? null : $error,
        ]);
    }

    /**
     * @return bool
     */
    private function isAjaxRequest(): bool
    {
        $ajaxHeader = 'HTTP_X_REQUESTED_WITH';

        return ! empty($_SERVER[$ajaxHeader]) && strtolower($_SERVER[$ajaxHeader]) == 'xmlhttprequest';
    }

    /**
     * @param \stdClass|array $error
     * @return null|int
     */
    private function getErrorType($error): ?int
    {
        if (is_object($error)) {
            return $error->type ?? null;
        }

        return $error['type'] ?? null;
    }

    /**
     * @param \stdClass|array $error
     * @return bool
     */
    private function isRecoverableError($error): bool
    {
        if ( ! $errorType = $this->getErrorType($error)) {
            return false;
        }

        return ! in_array($errorType, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]);
    }
}