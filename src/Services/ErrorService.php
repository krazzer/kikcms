<?php
declare(strict_types=1);

namespace KikCMS\Services;


use Error;
use Exception;
use KikCMS\Classes\Monolog\PhalconHtmlFormatter;
use KikCMS\Classes\Phalcon\Injectable;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;
use Phalcon\Http\ResponseInterface;
use stdClass;
use Swift_Message;

class ErrorService extends Injectable
{
    /**
     * @param int $deDuplicationTime
     * @return AbstractHandler|DeduplicationHandler|null
     */
    public function getEmailHandler(int $deDuplicationTime = 60): AbstractHandler|DeduplicationHandler|null
    {
        if( ! $developerEmail = $this->config->application->developerEmail){
            return null;
        }

        if( ! $domain = $this->config->application->domain){
            if( ! $domain = $_SERVER['HTTP_HOST'] ?? null){
                return null;
            }
        }

        $errorFromMail = 'error@' . $domain;

        $message = new Swift_Message('Error');
        $message->setFrom($errorFromMail);
        $message->setTo($developerEmail);
        $message->setContentType('text/html');

        $handler = new SwiftMailerHandler($this->mailer, $message, Logger::NOTICE);
        $handler->setFormatter(new PhalconHtmlFormatter);

        return new DeduplicationHandler($handler, null, Logger::ERROR, $deDuplicationTime);
    }

    /**
     * @param int $deDuplicationTime
     * @return Logger
     */
    public function getEmailLogger(int $deDuplicationTime = 60): Logger
    {
        $logger = new Logger('logger');

        $logger->pushHandler($this->getEmailHandler($deDuplicationTime));

        return $logger;
    }

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

        if (@$this->request->isAjax() && ! $isProduction) {
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
        $title       = @$this->translator->tl('error.' . $errorType . '.title');
        $description = @$this->translator->tl('error.' . $errorType . '.description', $parameters);

        if (@$this->request->isAjax() && $this->config->isProd()) {
            return $this->response->setJsonContent(['title' => $title, 'description' => $description]);
        } else {
            if(@$this->config->isProd()){
                return @$this->frontendService->getMessageResponse($title, $description);
            } else {
                $content = @$this->view->getPartial('@kikcms/errors/show' . $errorType, $parameters);
                return @$this->response->setContent($content);
            }
        }
    }

    /**
     * @param mixed $error
     */
    public function handleError(mixed $error): void
    {
        $isProduction = @$this->config->isProd();

        if( ! $errorView = $this->getErrorView($error, $isProduction)){
            return;
        }

        echo $this->getResponse($errorView, ['error' => $isProduction ? null : $error])->getContent();
    }

    /**
     * @param mixed $error
     * @return bool
     */
    public function isRecoverableError(mixed $error): bool
    {
        if ( ! $errorType = $this->getErrorType($error)) {
            return false;
        }

        return ! in_array($errorType, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_DEPRECATED]);
    }

    /**
     * @param mixed $error
     * @return null|int
     */
    private function getErrorType(Exception|Error|stdClass|array $error): ?int
    {
        if (is_object($error)) {
            return $error->type ?? null;
        }

        return $error['type'] ?? null;
    }
}