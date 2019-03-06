<?php /** @noinspection PhpUnusedParameterInspection */

namespace KikCMS\Plugins;


use Phalcon\Events\Event;
use Phalcon\Http\Response;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\User\Plugin;

/**
 */
class PlaceholderConverterPlugin extends Plugin
{
    /**
     * This action is executed before any response is shown
     *
     * @param Event $event
     * @param $app
     * @param $response
     */
    public function beforeSendResponse(Event $event, Application $app, Response $response)
    {
        $response->setContent($response->getContent());
    }
}