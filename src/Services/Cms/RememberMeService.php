<?php declare(strict_types=1);


namespace KikCMS\Services\Cms;


use DateInterval;
use DateTime;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\User;
use KikCMS\ObjectLists\RememberMeHashList;
use KikCMS\Objects\RememberMeHash;
use KikCMS\Services\UserService;
use Phalcon\Di\Injectable;

/**
 * @property UserService $userService
 */
class RememberMeService extends Injectable
{
    const COOKIE_KEY = 'remember-me';

    /**
     * Add a token for the current loggedin user and store it in a cookie
     */
    public function addToken()
    {
        $hashList    = $this->getForCurrentUser();
        $expire      = $this->getExpireDate();
        $cookieToken = $this->generateToken();

        $hashList->add(new RememberMeHash($expire, $this->security->hash($cookieToken)));

        $this->store($hashList);

        $this->cookies->set($this->getKey(), $cookieToken, $expire->getTimestamp(), '/', true)->send();
    }

    /**
     * Check whether there is a valid remember cookie available, and return the user id if so
     *
     * @return int|null
     */
    public function getUserIdByCookie(): ?int
    {
        // there is no cookie
        if ( ! $cookieToken = $this->cookies->get($this->getKey())->getValue()) {
            return null;
        }

        $userId = (int) explode('.', $cookieToken)[0];

        // the user doesn't exist
        if ( ! $user = User::getById($userId)) {
            return null;
        }

        $hashList = $this->getByUser($user);

        foreach ($hashList as $hash) {
            // check if hash matches
            if ( ! $this->security->checkHash($cookieToken, $hash->getHash())) {
                continue;
            }

            // check if expired
            if ($hash->getExpire() < new DateTime()) {
                continue;
            }

            // success
            return $userId;
        }

        return null;
    }

    /**
     * @param User $user
     * @return RememberMeHashList
     */
    private function getByUser(User $user): RememberMeHashList
    {
        if ( ! $user->getRememberMe()) {
            return new RememberMeHashList();
        }

        return unserialize($user->getRememberMe());
    }

    /**
     * @return RememberMeHashList
     */
    private function getForCurrentUser(): RememberMeHashList
    {
        return $this->getByUser($this->userService->getUser());
    }

    /**
     * @param RememberMeHashList $hashList
     */
    private function store(RememberMeHashList $hashList)
    {
        $user = $this->userService->getUser();

        $this->cleanUpExpiredTokens($hashList);

        $user->setRememberMe($hashList->serialize());
        $user->save();
    }

    /**
     * Remove token that is bound to the user's cookie. Also unset the cookie
     */
    public function removeToken()
    {
        if ( ! $cookieToken = $this->cookies->get($this->getKey())->getValue()) {
            return;
        }

        $hashList = $this->getForCurrentUser();

        foreach ($hashList as $i => $hash) {
            if ($this->security->checkHash($cookieToken, $hash->getHash())) {
                $hashList->remove($i);
            }
        }

        $this->store($hashList);

        $this->cookies->get($this->getKey())->delete();
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function generateToken(): string
    {
        return $this->userService->getUserId() . '.' . bin2hex(random_bytes(8));
    }

    /**
     * @return DateTime
     */
    private function getExpireDate(): DateTime
    {
        return (new DateTime)->add(new DateInterval('P30D'));
    }

    /**
     * @return string
     */
    private function getKey(): string
    {
        // Add port to cookie in dev, so different ports can be used
        if($this->config->application->env == KikCMSConfig::ENV_DEV){
            return self::COOKIE_KEY . '-' . $this->request->getPort();
        }

        return self::COOKIE_KEY;
    }

    /**
     * @param RememberMeHashList $hashList
     */
    private function cleanUpExpiredTokens(RememberMeHashList $hashList)
    {
        foreach ($hashList as $i => $hash) {
            if ($hash->getExpire() < new DateTime()) {
                $hashList->remove($i);
            }
        }
    }
}