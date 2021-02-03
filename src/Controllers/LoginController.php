<?php declare(strict_types=1);

namespace KikCMS\Controllers;

use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\Phalcon\KeyValue;
use KikCMS\Classes\Translator;
use KikCMS\Config\PassResetConfig;
use KikCMS\Forms\LoginForm;
use KikCMS\Forms\PasswordResetForm;
use KikCMS\Forms\PasswordResetLinkActivateForm;
use KikCMS\Forms\PasswordResetLinkForm;
use KikCMS\Models\User;
use KikCMS\Services\AssetService;
use KikCMS\Services\MailService;
use KikCMS\Services\UserService;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;

/**
 * @property AssetService $assetService
 * @property KeyValue $keyValue
 * @property MailService $mailService
 * @property Translator $translator
 * @property UserService $userService
 * @property WebsiteSettingsBase $websiteSettings
 */
class LoginController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->assetService->addCss('cmsassets/css/login.css');

        if ($customCss = $this->websiteSettings->getCustomCss()) {
            $this->assetService->addCss($customCss);
        }
    }

    /**
     * @inheritdoc
     */
    public function initializeLanguage()
    {
        if (isset($this->config->application->defaultCmsLanguage)) {
            $this->translator->setLanguageCode($this->config->application->defaultCmsLanguage);
        } else {
            $this->translator->setLanguageCode($this->config->application->defaultLanguage);
        }
    }

    /**
     * Displays the login form
     * @return null|Response|string
     */
    public function indexAction(): ResponseInterface
    {
        if ($this->userService->isLoggedIn()) {
            return $this->response->redirect('cms');
        }

        $loginForm = (new LoginForm)->render();

        if ($loginForm instanceof Response) {
            return $loginForm;
        }

        return $this->view('login/index', ['form' => $loginForm], 200);
    }

    /**
     * Displays the form to activate your account
     */
    public function activateAction()
    {
        $this->view->form = (new PasswordResetLinkActivateForm())->render();
    }

    /**
     * Displays the form to send you a password reset link
     */
    public function resetAction()
    {
        $this->view->form = (new PasswordResetLinkForm())->render();
    }

    /**
     * Displays the form to reset your password
     * @param User $user
     * @param string $token
     * @return ResponseInterface
     */
    public function resetPasswordAction(User $user, string $token): ResponseInterface
    {
        if ( ! $hashedToken = $this->keyValue->get(PassResetConfig::PREFIX . $user->getId(), PassResetConfig::LIFETIME)) {
            $errorMessage = $this->translator->tl('login.reset.password.expired');
            $this->flash->error($errorMessage);
            return $this->response->redirect('cms/login/reset');
        }

        if ( ! $this->security->checkHash($token, $hashedToken)) {
            $errorMessage = $this->translator->tl('login.reset.password.tokenError');
            $this->flash->error($errorMessage);
            return $this->response->redirect('cms/login');
        }

        $passwordForm = (new PasswordResetForm())->setUser($user)->render();

        if ($passwordForm instanceof Response) {
            return $passwordForm;
        }

        $this->flash->notice($this->translator->tl('login.reset.password.formMessage'));
        $this->view->form = $passwordForm;

        return $this->response->setContent($this->view->getPartial('login/reset'));
    }
}