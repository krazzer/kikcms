<?php

namespace KikCMS\Plugins;

use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Translator;
use KikCMS\Config\StatusCodes;
use KikCMS\Services\UserService;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;

/**
 * @property UserService $userService
 * @property Translator $translator
 * @property AccessControl $acl
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
        $controller = $dispatcher->getControllerName();

        //todo #3: this needs to be properly secured
        $isLoggedIn         = $controller == 'statistics' ? true : $this->userService->isLoggedIn();
        $allowedControllers = ['login', 'deploy', 'errors'];

        if ( ! $isLoggedIn && ! in_array($controller, $allowedControllers)) {
            if ($this->request->isAjax()) {
                $this->response->setStatusCode(StatusCodes::SESSION_EXPIRED, StatusCodes::SESSION_EXPIRED_MESSAGE);
            } else {
                if ($dispatcher->getActionName() != 'index') {
                    $this->flash->notice($this->translator->tl('login.expired'));
                }
                $this->response->redirect('cms/login');
            }

            return false;
        }

        if ($isLoggedIn && $controller == 'login') {
            $this->response->redirect('cms');
            return false;
        }

        // prevent unused parameter warning
        $event->setType($event->getType());

        return true;
    }
}
