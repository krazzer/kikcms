<?php declare(strict_types=1);

namespace KikCMS\Plugins;

use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Classes\Translator;
use KikCMS\Config\StatusCodes;
use KikCMS\Services\LanguageService;
use KikCMS\Services\UserService;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

/**
 * @property AccessControl $acl
 * @property LanguageService $languageService
 * @property Translator $translator
 * @property UserService $userService
 */
class SecurityPlugin extends Injectable
{
    const CONTROLLER_LOGIN      = 'login';
    const CONTROLLER_STATISTICS = 'statistics';
    const CONTROLLER_ERRORS     = 'statistics';

    const ALLOWED_CONTROLLERS = [
        self::CONTROLLER_LOGIN,
        self::CONTROLLER_STATISTICS,
        self::CONTROLLER_ERRORS
    ];

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
        $isLoggedIn = $controller == self::CONTROLLER_STATISTICS ?: $this->userService->isLoggedIn();

        if ( ! $isLoggedIn && ! in_array($controller, self::ALLOWED_CONTROLLERS)) {
            if ($this->request->isAjax()) {
                $this->response->setStatusCode(StatusCodes::SESSION_EXPIRED, StatusCodes::SESSION_EXPIRED_MESSAGE);
            } else {
                if ($dispatcher->getActionName() != 'index') {
                    $this->translator->setLanguageCode($this->languageService->getDefaultCmsLanguageCode());
                    $this->flash->notice($this->translator->tl('login.expired'));
                }
                $this->response->redirect('cms/login');
            }

            return false;
        }

        // prevent unused parameter warning
        $event->setType($event->getType());

        return true;
    }
}
