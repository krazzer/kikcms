<?php

namespace KikCMS\Classes\Phalcon;


use DateTime;
use KikCMS\Classes\Permission;
use Phalcon\Acl\Adapter\Memory;

class AccessControl extends Memory
{
    /** @var string */
    private $currentRole;

    /** @var DateTime */
    private $updated;

    /** @var int|null time in seconds when the acl should be updated */
    private $updateTime;

    /**
     * @param string $role
     */
    public function __construct(string $role)
    {
        $this->currentRole = $role;
        $this->updated     = new DateTime;
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
    public function allowed($resourceName, $access = Permission::ACCESS_TYPE_ANY, array $parameters = null): bool
    {
        return parent::isAllowed($this->currentRole, $resourceName, $access, $parameters);
    }

    /**
     * @return bool
     */
    public function canDeleteMenu(): bool
    {
        return $this->allowed(Permission::PAGE_MENU, Permission::ACCESS_TYPE_DELETE);
    }

    /**
     * Update the updated time to the current time
     */
    public function update()
    {
        $this->updated = new DateTime;
    }

    /**
     * @return int|null
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

    /**
     * @param int|null $updateTime
     * @return AccessControl
     */
    public function setUpdateTime($updateTime)
    {
        $this->updateTime = $updateTime;
        return $this;
    }

    /**
     * @return bool
     */
    public function requiresUpdate(): bool
    {
        if( ! $this->updateTime){
            return false;
        }

        $seconds = (new DateTime)->getTimestamp() - $this->updated->getTimestamp();

        return $seconds >= $this->updateTime;
    }
}