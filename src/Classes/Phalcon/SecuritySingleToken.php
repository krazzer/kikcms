<?php declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;

use Phalcon\Security;

/**
 * Use the same token while users' session is active. This is required for having multiple forms active at the same
 * time. Required for subDataTables.
 */
class SecuritySingleToken extends Security
{
    /**
     * @inheritdoc
     */
    public function getTokenKey(): string
    {
        $tokenKey = $this->getDI()->get('session')->get($this->tokenValueSessionId);

        if ($tokenKey) {
            return $tokenKey;
        }

        return parent::getTokenKey();
    }

    /**
     * @inheritdoc
     */
    public function getToken(): string
    {
        $token = $this->getDI()->get('session')->get($this->tokenValueSessionId);

        if ($token) {
            return $token;
        }

        return parent::getToken();
    }

    /**
     * @inheritdoc
     */
    public function checkToken($tokenKey = null, $tokenValue = null, $destroyIfValid = false): bool
    {
        return parent::checkToken($tokenKey, $tokenValue, $destroyIfValid);
    }
}