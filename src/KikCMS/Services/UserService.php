<?php

namespace KikCMS\Services;


use KikCMS\Classes\DbWrapper;
use Phalcon\Config;
use Phalcon\Di\Injectable;

/** @property DbWrapper $dbWrapper */
/** @property Config $applicationConfig */
class UserService extends Injectable
{
    /**
     * @param $email
     *
     * @return array
     */
    public function getByEmail($email)
    {
        $user = $this->dbWrapper->queryRow("
            SELECT * FROM kikcms_user 
            WHERE email = " . $this->dbWrapper->escape($email) . "
        ");

        return $user;
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

        if( ! $user){
            return false;
        }

        // password not yet set, returns true, but should not be allowed to login
        if( ! $user['password']){
            return true;
        }

        if (password_verify($password, $user['password'])) {
            if ( ! password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $this->setLoggedIn($user['id']);
                return true;
            }

            $this->storePassword($user['id'], $password);
            $this->setLoggedIn($user['id']);
            return true;
        }

        return false;
    }

    /**
     * @param int $id
     * @param string $password
     */
    public function storePassword(int $id, string $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->db->query("
            UPDATE kikcms_user
            SET password = " . $this->dbWrapper->escape($hash) . "
            WHERE id = " . (int) $id . "
        ");
    }

    /**
     * @param $user
     * @return bool
     */
    public function isActive($user)
    {
        return $user['active'] == 1 && $user['password'];
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
        $this->session->set('loggedIn', false);
        $this->session->set('userId', null);

        $this->flash->notice($this->translator->tl('login.logout'));
        $this->response->redirect('cms/login');
    }
}