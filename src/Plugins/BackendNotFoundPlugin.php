<?php declare(strict_types=1);

namespace KikCMS\Plugins;

use Exception;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use KikCMS\Classes\Exceptions\SessionExpiredException;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Config\StatusCodes;
use KikCMS\Services\UserService;
use Phalcon\Events\Event;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Mvc\User\Plugin;

/**
 * NotFoundPlugin
 *
 * Handles not-found controller/actions
 *
 * @property UserService $userService
 */
class BackendNotFoundPlugin extends Plugin
{
    const DISPATCH_ERRORS = [
        Dispatcher::EXCEPTION_HANDLER_NOT_FOUND,
        Dispatcher::EXCEPTION_ACTION_NOT_FOUND
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
            $params = $exception instanceof ObjectNotFoundException ? ['object' => $exception->getObject()] : [];

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
            break;
            case $exception instanceof ObjectNotFoundException:
                return ['show404object', null, false];
            break;
            case $exception instanceof NotFoundException || $isDispatchError:
                return ['show404', null, false];
            break;
            case $exception instanceof UnauthorizedException:
                return ['show401', 401, false];
            break;
            case $exception instanceof Exception:
                return ['show500', 500, false];
            break;
            default:
                return [null, null, true];
            break;
        }
    }
}
