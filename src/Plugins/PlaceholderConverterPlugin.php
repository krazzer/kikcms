<?php declare(strict_types=1);

namespace KikCMS\Plugins;


use KikCMS\Services\PlaceholderService;
use Phalcon\Events\Event;
use Phalcon\Http\Response;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\User\Plugin;

/**
 * @property PlaceholderService $placeholderService
 */
class PlaceholderConverterPlugin extends Plugin
{
    /**
     * This action is executed before any response is shown
     *
     * @noinspection PhpUnusedParameterInspection
     * @param Event $event
     * @param $app
     * @param $response
     */
    public function beforeSendResponse(Event $event, Application $app, Response $response)
    {
        if( ! $content = $response->getContent()){
            return;
        }

        $response->setContent($this->placeholderService->replaceAll($content));
    }
}