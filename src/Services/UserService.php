<?php

namespace KikCMS\Services;


use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Models\FinderFolder;
use KikCMS\ObjectLists\UserMap;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Translator;
use KikCMS\Models\User;
use Phalcon\Config;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property AccessControl $acl
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

        return $this->url->get('cms/login/reset-password') . '/' . $user->id . '/' . $hash . '/' . $time;
    }

    /**
     * @return User
     */
    public function getUser(): User
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
        return $this->session->get('role');
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

        if($this->config->get('company')->get('logoMail')){
            return $this->mailService->sendMailUser($user->email, $subject, $body, $parameters);
        } else {
            return $this->mailService->sendServiceMail($user->email, $subject, $body, $parameters);
        }
    }

    /**
     * @param $folderId
     * @return bool
     */
    public function allowedInFolderId($folderId): bool
    {
        if($this->acl->allowed(Permission::ACCESS_FINDER_FULL)){
            return true;
        }

        $folder = FinderFolder::getById($folderId);

        if( ! $folder){
            return false;
        }

        return $this->allowedInFolder($folder);
    }

    /**
     * @param FinderFolder $folder
     * @return bool
     */
    public function allowedInFolder(FinderFolder $folder): bool
    {
        if($this->acl->allowed(Permission::ACCESS_FINDER_FULL)){
            return true;
        }

        $userId = $this->getUserId();

        if($folder->user_id == $userId){
            return true;
        }

        if( ! $folder->folder){
            return false;
        }

        return $this->allowedInFolder($folder->folder);
    }

    /**
     * Get roles that are greater or equal to the role of the current logged in user
     * @return array
     */
    public function getGreaterAndEqualRoles(): array
    {
        $allRoles = Permission::ROLES;

        $roles = [];

        $currentRoleIndex = null;

        foreach ($allRoles as $roleIndex => $role){
            if($role == $this->getRole()){
                $currentRoleIndex = $roleIndex;
            }

            if($currentRoleIndex !== null && $roleIndex <= $currentRoleIndex){
                $roles[] = $role;
            }
        }

        return $roles;
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
     * Get roles that are below or equal to the role of the current logged in user
     * @return array
     */
    public function getSubordinateAndEqualRoles(): array
    {
        $allRoles = Permission::ROLES;

        $roles = [];

        $currentRoleIndex = null;

        foreach ($allRoles as $roleIndex => $role){
            if($role == $this->getRole()){
                $currentRoleIndex = $roleIndex;
            }

            if($currentRoleIndex !== null && $roleIndex >= $currentRoleIndex){
                $roles[] = $role;
            }
        }

        return $roles;
    }

    /**
     * Get users that are below or equal to the role of the current logged in user
     * @return array
     */
    public function getSubordinateAndEqualUserIds(): array
    {
        $roles = $this->getSubordinateAndEqualRoles();

        $query = (new Builder)
            ->columns(User::FIELD_ID)
            ->from(User::class)
            ->inWhere(User::FIELD_ROLE, $roles);

        return $this->dbService->getValues($query);
    }
}