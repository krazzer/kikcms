<?php declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;


use KikCMS\Config\CacheConfig;
use Phalcon\Encryption\Security;

/**
 * Use the same token while users' session is active. This is required for having multiple forms active at the same
 * time. Required for subDataTables.
 */
class SecurityKeyValue extends Security
{
    /** @var string|null */
    private ?string $keyValueTokenKey = null;

    /**
     * @inheritdoc
     */
    public function getTokenKey(): string
    {
        if ($this->keyValueTokenKey) {
            return $this->keyValueTokenKey;
        } else {
            $tokenKey   = bin2hex(random_bytes(8));
            $tokenValue = bin2hex(random_bytes(32));

            $this->keyValueTokenKey = $tokenKey;

            $this->getKeyValue()->set($tokenKey, $tokenValue, CacheConfig::ONE_DAY);

            return $tokenKey;
        }
    }

    /**
     * @inheritdoc
     */
    public function getToken(): string
    {
        $tokenKey = $this->getTokenKey();

        return $this->getKeyValue()->get($tokenKey);
    }

    /**
     * @inheritDoc
     */
    public function checkToken($tokenKey = null, $tokenValue = null, bool $destroyIfValid = false): bool
    {
        $tokens = $this->getDI()->get('request')->getPost('token') ?? [];

        foreach ($tokens as $tokenKey => $tokenValue) {
            return $this->getKeyValue()->get($tokenKey) === $tokenValue;
        };

        return false;
    }

    /**
     * @return KeyValue
     */
    public function getKeyValue(): KeyValue
    {
        return $this->getDI()->get('keyValue');
    }
}