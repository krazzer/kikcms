<?php

namespace KikCMS\Classes\Phalcon;


use Phalcon\Acl\Adapter\Memory;

class AccessControl extends Memory
{
    /** @var string */
    private $currentRole;

    /**
     * @param string $role
     */
    public function __construct(string $role)
    {
        $this->currentRole = $role;
    }

    /**
     * Shortcut to check if access is allowed for the current logged in user's role
     *
     * @param $resourceName
     * @param $access
     * @param array|null $parameters
     *
     * @return bool
     */
    public function allowed($resourceName, $access = '*', array $parameters = null): bool
    {
        return parent::isAllowed($this->currentRole, $resourceName, $access, $parameters);
    }
}