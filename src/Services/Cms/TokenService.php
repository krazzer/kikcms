<?php

namespace KikCMS\Services\Cms;

use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\TokenConfig;

/**
 * Service for creating and verifing tokens
 */
class TokenService extends Injectable
{
    /**
     * @return string
     */
    public function createToken(): string
    {
        $token       = $this->stringService->createRandomString();
        $hashedToken = $this->security->hash($token);

        $this->keyValue->set($this->getName($token), $hashedToken, TokenConfig::LIFETIME);

        return $token;
    }

    /**
     * @param string $token
     * @return bool
     */
    public function isValid(string $token): bool
    {
        if( ! $hash = $this->keyValue->get($this->getName($token))){
            return false;
        }

        return $this->security->checkHash($token, $hash);
    }

    /**
     * @param string $token
     * @return string
     */
    private function getName(string $token): string
    {
        return TokenConfig::PREFIX . '_' . substr($token, 0, 8);
    }
}