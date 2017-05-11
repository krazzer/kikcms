<?php

namespace KikCMS\Services;


use KikCMS\Classes\DbService;
use KikCMS\Classes\Translator;
use KikCMS\Models\KikcmsUser;
use Phalcon\Config;
use Phalcon\Di\Injectable;

/**
 * @property DbService $dbService
 * @property Config $applicationConfig
 * @property Translator $translator
 */
class UserService extends Injectable
{
    /**
     * @param $email
     *
     * @return KikcmsUser
     */
    public function getByEmail($email)
    {
        return KikcmsUser::findFirst('email = ' . $this->dbService->escape($email));
    }

    /**
     * @param KikcmsUser $user
     * @return string
     */
    public function getResetUrl(KikcmsUser $user): string
    {
        $time = date('U');
        $hash = $this->security->hash($user->id . $time);

        return $this->url->get('cms/login/reset-password') . '?userId=' . $user->id . '&hash=' . $hash . '&t=' . $time;
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
                $this->setLoggedIn($user->id);
                return true;
            }

            $this->storePassword($user, $password);
            $this->setLoggedIn($user->id);

            return true;
        }

        return false;
    }

    /**
     * @param KikcmsUser $user
     * @param string $password
     */
    public function storePassword(KikcmsUser $user, string $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $user->password = $hashedPassword;
        $user->save();
    }

    /**
     * @param KikcmsUser $user
     * @return bool
     */
    public function isActive(KikcmsUser $user)
    {
        return $user->active == 1 && $user->password;
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
    private function setLoggedIn(int $id)
    {
        $this->session->set('loggedIn', true);
        $this->session->set('userId', $id);
    }

    public function logout()
    {
        // remove current session data
        $this->session->destroy();

        // start a new session so we can still flash
        $this->session->start();
        $this->flash->notice($this->translator->tl('login.logout'));
        $this->response->redirect('cms/login');
    }
}