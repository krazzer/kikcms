<?php declare(strict_types=1);

namespace KikCMS\Plugins;


use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Services\PlaceholderService;
use Phalcon\Events\Event;
use Phalcon\Http\Response;
use Phalcon\Mvc\Application;

/**
 * @property PlaceholderService $placeholderService
 */
class PlaceholderConverterPlugin extends Injectable
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