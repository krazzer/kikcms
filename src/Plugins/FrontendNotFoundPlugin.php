<?php declare(strict_types=1);

namespace KikCMS\Plugins;

use Exception;
use KikCMS\Classes\Exceptions\DatabaseConnectionException;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Phalcon\Injectable;
use KikCmsCore\Exceptions\ResourcesExceededException;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

/**
 * NotFoundPlugin
 *
 * Handles not-found controller/actions
 */
class FrontendNotFoundPlugin extends Injectable
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
    public function beforeException(Event $event, Dispatcher $dispatcher, Exception $exception): bool
    {
        if ($exception instanceof ResourcesExceededException) {
            $dispatcher->forward([
                'namespace'  => "KikCMS\\Controllers",
                'controller' => 'frontend',
                'action'     => 'resourcesExceeded',
            ]);

            return false;
        }

        if ($exception instanceof DatabaseConnectionException) {
            $dispatcher->forward([
                'namespace'  => "KikCMS\\Controllers",
                'controller' => 'frontend',
                'action'     => 'databaseConnectionFailure',
            ]);

            return false;
        }

        if ($exception instanceof NotFoundException) {
            $dispatcher->forward([
                'namespace'  => "KikCMS\\Controllers",
                'controller' => 'frontend',
                'action'     => 'pageNotFound',
                "params"     => ['languageCode' => $exception->getLanguageCode()]
            ]);

            return false;
        }

        if ($exception instanceof UnauthorizedException) {
            $dispatcher->forward([
                'namespace'  => "KikCMS\\Controllers",
                'controller' => 'frontend',
                'action'     => 'unauthorized',
            ]);

            return false;
        }

        // prevent unused parameter warning
        $event->setType($event->getType());

        return true;
    }
}
