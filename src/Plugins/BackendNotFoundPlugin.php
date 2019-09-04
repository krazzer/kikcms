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
        $isDispatchError = $exception instanceof DispatcherException && in_array($exception->getCode(), self::DISPATCH_ERRORS);

        switch (true) {
            case $exception instanceof SessionExpiredException:
                $this->response->setStatusCode(StatusCodes::SESSION_EXPIRED, StatusCodes::SESSION_EXPIRED_MESSAGE);
            break;
            case $exception instanceof ObjectNotFoundException:
                $this->forwardErrorView($dispatcher, 'show404object', [$exception->getObject()]);
            break;
            case $exception instanceof NotFoundException || $isDispatchError:
                $this->forwardErrorView($dispatcher, 'show404');
            break;
            case $exception instanceof UnauthorizedException:
                $this->response->setStatusCode(401);
                $this->forwardErrorView($dispatcher, 'show401');
            break;
            default:
                return true;
            break;
        }

        // prevent unused parameter warning
        $event->setType($event->getType());

        return false;
    }

    /**
     * @param MvcDispatcher $dispatcher
     * @param string $view
     * @param array $params
     */
    private function forwardErrorView(MvcDispatcher $dispatcher, string $view, array $params = [])
    {
        $dispatcher->forward([
            'namespace'  => KikCMSConfig::NAMESPACE_PATH_CMS_CONTROLLERS,
            'controller' => 'errors',
            'action'     => $view,
            'params'     => $params,
        ]);
    }
}
