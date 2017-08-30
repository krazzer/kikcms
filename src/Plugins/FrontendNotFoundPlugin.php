<?php

namespace KikCMS\Plugins;

use Exception;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

/**
 * NotFoundPlugin
 *
 * Handles not-found controller/actions
 */
class FrontendNotFoundPlugin extends Plugin
{
    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @param Exception $exception
     *
     * @return bool
     */
    public function beforeException(Event $event, Dispatcher $dispatcher, Exception $exception)
    {
        //todo: this is a temp fix because forwarding from an exception doesn't work until Phalcon 3.2, fix after update
        if ($exception instanceof ObjectNotFoundException) {
            http_response_code(404);
            echo 'Object not found';
            exit;
        }

        if ($exception instanceof NotFoundException) {
            $this->response->setStatusCode(404);

            $dispatcher->forward([
                'controller' => 'frontend',
                'action'     => 'pageNotFound',
                "params"     => ['languageCode' => $exception->getLanguageCode()]
            ]);

            return false;
        }

        // prevent unused parameter warning
        $event->setType($event->getType());

        return true;
    }
}
