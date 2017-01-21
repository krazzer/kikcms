<?php

namespace KikCMS\Classes\Phalcon;


use Phalcon\Di;

/**
 * Use the same token while users' session is active. This is required for having multiple forms active at the same
 * time. Required for subDataTables.
 */
class Security extends \Phalcon\Security
{
    /**
     * @inheritdoc
     */
    public function getTokenKey()
    {
        $tokenKey = Di::getDefault()->get('session')->get($this->_tokenKeySessionID);

        if ($tokenKey) {
            return $tokenKey;
        }

        return parent::getTokenKey();
    }

    /**
     * @inheritdoc
     */
    public function getToken()
    {
        $token = Di::getDefault()->get('session')->get($this->_tokenValueSessionID);

        if ($token) {
            return $token;
        }

        return parent::getToken();
    }

    /**
     * @inheritdoc
     */
    public function checkToken($tokenKey = null, $tokenValue = null, $destroyIfValid = false)
    {
        return parent::checkToken($tokenKey, $tokenValue, $destroyIfValid);
    }
}