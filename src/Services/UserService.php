<?php

namespace KikCMS\Services;


use KikCmsCore\Services\DbService;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Translator;
use KikCMS\Models\User;
use Phalcon\Config;
use Phalcon\Di\Injectable;

/**
 * @property DbService $dbService
 * @property Config $applicationConfig
 * @property Translator $translator
 * @property MailService $mailService
 */
class UserService extends Injectable
{
    /**
     * @param string $password
     * @return string
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @param $email
     *
     * @return User
     */
    public function getByEmail($email)
    {
        return User::findFirst('email = ' . $this->dbService->escape($email));
    }

    /**
     * @param User $user
     * @return string
     */
    public function getResetUrl(User $user): string
    {
        $time = date('U');
        $hash = $this->security->hash($user->id . $time);

        return $this->url->get('cms/login/reset-password') . '?userId=' . $user->id . '&hash=' . $hash . '&t=' . $time;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return (int) $this->session->get('userId');
    }

    /**
     * Determine whether a given email and password are allowed to login or must still be activated
     *
     * @param string $email
     * @param string $password
     *
     * @return bool
     */
    public function isValidOrNotActivatedYet(string $email, string $password): bool
    {
        $user = $this->getByEmail($email);

        if ( ! $user) {
            return false;
        }

        // password not yet set, returns true, but should not be allowed to login
        if ( ! $user->password) {
            return true;
        }

        if (password_verify($password, $user->password)) {
            if ( ! password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
                return true;
            }

            $this->storePassword($user, $password);
            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param string $password
     */
    public function storePassword(User $user, string $password)
    {
        $user->password = $this->hashPassword($password);
        $user->save();
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isActive(User $user): bool
    {
        return (bool) $user->password;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->session->get('loggedIn', false);
    }

    /**
     * @param $id
     */
    public function setLoggedIn(int $id)
    {
        $user = User::getById($id);

        $this->session->set('loggedIn', true);
        $this->session->set('userId', $id);
        $this->session->set('role', $user->role);
    }

    /**
     * Log the user out and redirect him to the login page
     */
    public function logout()
    {
        // remove current session data
        $this->session->destroy();

        // start a new session so we can still flash
        $this->session->start();
        $this->flash->notice($this->translator->tl('login.logout'));
        $this->response->redirect('cms/login');
    }

    /**
     * @param User $user
     * @return bool
     */
    public function sendResetMail(User $user): bool
    {
        $subject     = $this->translator->tl('login.reset.mail.subject');
        $body        = $this->translator->tl('login.reset.mail.body');
        $buttonLabel = $this->translator->tl('login.reset.mail.buttonLabel');

        $resetUrl = $this->getResetUrl($user);

        $parameters['buttons'] = [
            'reset' => ['url' => $resetUrl, 'label' => $buttonLabel]
        ];

        if($user->role == Permission::CLIENT){
            return $this->mailService->sendMailUser($user->email, $subject, $body, $parameters);
        } else {
            return $this->mailService->sendServiceMail($user->email, $subject, $body, $parameters);
        }
    }
}