<?php declare(strict_types=1);

namespace KikCMS\Plugins;

use Exception;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use KikCMS\Classes\Exceptions\SessionExpiredException;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Config\StatusCodes;
use KikCMS\Services\UserService;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;

/**
 * NotFoundPlugin
 *
 * Handles not-found controller/actions
 *
 * @property UserService $userService
 */
class BackendNotFoundPlugin extends Injectable
{
    const DISPATCH_ERRORS = [
        DispatcherException::EXCEPTION_HANDLER_NOT_FOUND,
        DispatcherException::EXCEPTION_ACTION_NOT_FOUND
    ];

    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param MvcDispatcher $dispatcher
     * @param Exception $exception
     *
     * @return bool
     */
    public function beforeException(Event $event, MvcDispatcher $dispatcher, Exception $exception)
    {
        list($forwardView, $statusCode, $return) = $this->getActionForException($exception);

        if ($statusCode) {
            $this->response->setStatusCode($statusCode);
        }

        if ($forwardView) {
            $params = ['error' => $exception];

            if ($exception instanceof ObjectNotFoundException) {
                $params = ['object' => $exception->getObject()];
            }

            $dispatcher->forward([
                'namespace'  => KikCMSConfig::NAMESPACE_PATH_CMS_CONTROLLERS,
                'controller' => 'errors',
                'action'     => $forwardView,
                'params'     => $params,
            ]);
        }

        // prevent unused parameter warning
        $event->setType($event->getType());

        return $return;
    }

    /**
     * Get the action for given exception
     *
     * @param Exception $exception
     * @return array [string viewToForwardTo, int statusCode, bool returnValue]
     */
    public function getActionForException(Exception $exception): array
    {
        $isDispatchError = $exception instanceof DispatcherException && in_array($exception->getCode(), self::DISPATCH_ERRORS);

        switch (true) {
            case $exception instanceof SessionExpiredException:
                return [null, StatusCodes::SESSION_EXPIRED, false];
            case $exception instanceof ObjectNotFoundException:
                return ['show404object', null, false];
            case $exception instanceof NotFoundException || $isDispatchError:
                return ['show404', null, false];
            case $exception instanceof UnauthorizedException:
                return ['show401', 401, false];
            default:
                return [null, null, true];
        }
    }
}
