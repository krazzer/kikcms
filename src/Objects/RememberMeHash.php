<?php


namespace KikCMS\Objects;


use DateTime;

class RememberMeHash
{
    /** @var DateTime */
    private $expire;

    /** @var string */
    private $hash;

    /**
     * @param DateTime $expire
     * @param string $hash
     */
    public function __construct(DateTime $expire, string $hash)
    {
        $this->expire = $expire;
        $this->hash   = $hash;
    }

    /**
     * @return DateTime
     */
    public function getExpire(): DateTime
    {
        return $this->expire;
    }

    /**
     * @param DateTime $expire
     * @return RememberMeHash
     */
    public function setExpire(DateTime $expire): RememberMeHash
    {
        $this->expire = $expire;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return RememberMeHash
     */
    public function setHash(string $hash): RememberMeHash
    {
        $this->hash = $hash;
        return $this;
    }
}