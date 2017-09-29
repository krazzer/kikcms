<?php

namespace KikCMS\Plugins;

use Exception;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\SessionExpiredException;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Config\StatusCodes;
use Phalcon\Events\Event;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Mvc\User\Plugin;

/**
 * NotFoundPlugin
 *
 * Handles not-found controller/actions
 */
class BackendNotFoundPlugin extends Plugin
{
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
        $controller = $dispatcher->getControllerName();
        $isLoggedIn = $this->userService->isLoggedIn();

        if ($controller == 'cms' && ! $isLoggedIn) {
            $this->response->redirect('cms/login');
            return false;
        }

        if ($exception instanceof SessionExpiredException) {
            $this->response->setStatusCode(StatusCodes::SESSION_EXPIRED, StatusCodes::SESSION_EXPIRED_MESSAGE);
            return false;
        }

        if ($exception instanceof DispatcherException) {
            switch ($exception->getCode()) {
                case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward([
                        'namespace'  => "KikCMS\\Controllers",
                        'controller' => 'errors',
                        'action'     => 'show404'
                    ]);

                    return false;
                break;
            }
        }

        if ($exception instanceof NotFoundException) {
            $dispatcher->forward([
                'namespace'  => "KikCMS\\Controllers",
                'controller' => 'errors',
                'action'     => 'show404'
            ]);

            return false;
        }

        if ($exception instanceof UnauthorizedException) {
            $this->response->setStatusCode(401);
            $dispatcher->forward([
                'namespace'  => "KikCMS\\Controllers",
                'controller' => 'errors',
                'action'     => 'show401'
            ]);

            return false;
        }

        // prevent unused parameter warning
        $event->setType($event->getType());

        return true;
    }
}
