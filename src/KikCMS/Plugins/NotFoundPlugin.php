<?php

namespace KikCMS\Plugins;

use KikCMS\Config\KikCMSConfig;
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
class NotFoundPlugin extends Plugin
{
    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param MvcDispatcher $dispatcher
     * @param \Exception $exception
     * @return bool
     */
    public function beforeException(Event $event, MvcDispatcher $dispatcher, \Exception $exception)
    {
        error_log($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());

        if ($exception instanceof DispatcherException) {
            switch ($exception->getCode()) {
                case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward([
                        'controller' => 'errors',
                        'action'     => 'show404'
                    ]);

                    return false;
                break;
            }
        }

        // display exceptions right away in development
        if($this->applicationConfig->env == KikCMSConfig::ENV_DEV){
            return true;
        }

        $dispatcher->forward([
            'controller' => 'errors',
            'action'     => 'show500'
        ]);

        return false;
    }
}
