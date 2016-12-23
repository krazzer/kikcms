<?php

namespace KikCMS\Plugins;

use KikCMS\Services\UserService;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;

/**
 * @property UserService $userService
 */
class SecurityPlugin extends Plugin
{
    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @return bool
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        $controller         = $dispatcher->getControllerName();
        $isLoggedIn         = $this->userService->isLoggedIn();
        $allowedControllers = ['login', 'deploy', 'errors'];

        if (!$isLoggedIn && !in_array($controller, $allowedControllers)) {
            $this->response->redirect('cms/login');
            return false;
        }

        return true;
    }
}
