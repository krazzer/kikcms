<?php declare(strict_types=1);

namespace KikCMS\Services;


use Exception;
use KikCMS\Classes\Database\Now;
use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\PassResetConfig;
use KikCMS\ObjectLists\UserMap;
use KikCMS\Classes\Permission;
use KikCMS\Models\User;
use Monolog\Logger;
use Phalcon\Mvc\Model\Query\Builder;

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
        $token       = $this->stringService->createRandomString();
        $hashedToken = $this->security->hash($token);

        $this->keyValue->save(PassResetConfig::PREFIX . $user->getId(), $hashedToken, PassResetConfig::LIFETIME);

        return $this->url->get('cms/login/reset-password') . '/' . $user->id . '/' . $token;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return User::getById($this->getUserId());
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return (int) $this->session->get('userId');
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->session->get('role') ?: Permission::VISITOR;
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
        // trim password so accidentally added spaces are removed
        $password = trim($password);

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
        if ($this->session->get('loggedIn', false)) {
            return true;
        }

        if ( ! $userId = $this->rememberMeService->getUserIdByCookie()) {
            return false;
        }

        $this->setLoggedIn($userId);
        return true;
    }

    /**
     * @param $id
     */
    public function setLoggedIn(int $id)
    {
        // Clean up disk cache. If this fails for any reason, don't bother the user
        try {
            $this->cmsService->cleanUpDiskCache();
        } catch (Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception);
        }

        $user = User::getById($id);

        $user->last_login = (new Now);
        $user->save();

        $this->session->set('loggedIn', true);
        $this->session->set('userId', $id);
        $this->session->set('role', $user->role);
    }

    /**
     * Log the user out and redirect him to the login page
     */
    public function logout()
    {
        $this->rememberMeService->removeToken();

        // remove current session data
        $this->session->destroy();
        $this->permission->reset();

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

        $parameters = [
            'buttons'       => ['reset' => ['url' => $resetUrl, 'label' => $buttonLabel]],
            'plainTextBody' => $body . "\n\n" . $resetUrl,
        ];

        if ($this->getRole() == Permission::CLIENT) {
            return (bool) $this->mailService->sendMailUser($user->email, $subject, $body, $parameters);
        } else {
            return (bool) $this->mailService->sendServiceMail($user->email, $subject, $body, $parameters);
        }
    }

    /**
     * @return UserMap
     */
    public function getMap(): UserMap
    {
        $query = (new Builder)
            ->from(User::class)
            ->orderBy(User::FIELD_EMAIL);

        return $this->dbService->getObjectMap($query, UserMap::class);
    }

    /**
     * Get users that by given roles
     * @param array $roles
     * @return UserMap
     */
    public function getByRoles(array $roles): UserMap
    {
        $query = (new Builder)
            ->from(User::class)
            ->inWhere(User::FIELD_ROLE, $roles);

        return $this->dbService->getObjectMap($query, UserMap::class);
    }
}