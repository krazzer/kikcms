<?php declare(strict_types=1);

namespace KikCMS\Controllers;

use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\Permission;
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
    public function initialize(): void
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
    public function initializeLanguage(): void
    {
        $this->translator->setLanguageCode($this->languageService->getDefaultCmsLanguageCode());
    }

    /**
     * @param User $user
     * @return ResponseInterface
     */
    public function impersonateAction(User $user): ResponseInterface
    {
        if( ! $this->permission->isAdmin() || in_array($user->role, Permission::ADMIN_ROLES)){
            $this->flash->error($this->translator->tl('permissions.noImpersonateAcces'));
            return $this->response->redirect($this->url->get('cms/users'));
        }

        $this->flash->success($this->translator->tl('permissions.impersonated', ['email' => $user->email]));
        $this->userService->setLoggedIn($user->getId());

        return $this->response->redirect($this->url->get('cms'));
    }

    /**
     * Displays the login form
     * @return null|ResponseInterface|string
     */
    public function indexAction(): null|ResponseInterface|string
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
     * @return ResponseInterface
     */
    public function activateAction(): ResponseInterface
    {
        $form = (new PasswordResetLinkActivateForm())->render();

        return $this->view('login/activate', ['form' => $form]);
    }

    /**
     * Displays the form to send you a password reset link
     * @return ResponseInterface
     */
    public function resetAction(): ResponseInterface
    {
        $form = (new PasswordResetLinkForm())->render();

        return $this->view('login/reset', ['form' => $form], 200);
    }

    /**
     * Displays the form to reset your password
     * @param User $user
     * @param string $token
     * @return ResponseInterface
     */
    public function resetPasswordAction(User $user, string $token): ResponseInterface
    {
        if ( ! $hashedToken = $this->keyValue->get(PassResetConfig::PREFIX . $user->getId() . $token)) {
            $errorMessage = $this->translator->tl('login.reset.password.expired');
            $this->flash->error($errorMessage);
            return $this->response->redirect('cms/login/reset');
        }

        if ( ! $this->security->checkHash($token, $hashedToken)) {
            $errorMessage = $this->translator->tl('login.reset.password.tokenError');
            $this->flash->error($errorMessage);
            return $this->response->redirect('cms/login');
        }

        $passwordForm = (new PasswordResetForm)->setUser($user)->render();

        if ($passwordForm instanceof Response) {
            return $passwordForm;
        }

        $this->flash->notice($this->translator->tl('login.reset.password.formMessage'));

        return $this->view('login/reset', ['form' => $passwordForm], 200);
    }
}