<?php declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;


use DateTime;
use KikCMS\Classes\Permission;
use Phalcon\Acl\Adapter\Memory;

class AccessControl extends Memory
{
    /** @var string */
    private string $currentRole;

    /** @var DateTime */
    private DateTime $updated;

    /** @var int|null time in seconds when the acl should be updated */
    private ?int $updateTime;

    /**
     * @param string $role
     */
    public function __construct(string $role)
    {
        parent::__construct();

        $this->currentRole = $role;
        $this->updated     = new DateTime;
    }

    /**
     * @inheritdoc
     */
    public function addComponent($resourceValue, $accessList = Permission::ACCESS_ANY): bool
    {
        return parent::addComponent($resourceValue, $accessList);
    }

    /**
     * @inheritdoc
     */
    public function allow(string $roleName, string $resourceName, $access = Permission::ACCESS_ANY, $func = null): void
    {
        parent::allow($roleName, $resourceName, $access, $func);
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
    public function allowed($resourceName, $access = Permission::ACCESS_ANY, array $parameters = null): bool
    {
        return parent::isAllowed($this->currentRole, $resourceName, $access, $parameters);
    }

    /**
     * @return bool
     */
    public function canDeleteMenu(): bool
    {
        return $this->allowed(Permission::PAGE_MENU, Permission::ACCESS_DELETE);
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
        if ( ! $this->updateTime) {
            return false;
        }

        $seconds = (new DateTime)->getTimestamp() - $this->updated->getTimestamp();

        return $seconds >= $this->updateTime;
    }

    /**
     * @param string $resourceName
     * @return bool
     */
    public function resourceExists(string $resourceName): bool
    {
        foreach ($this->getResources() as $resource) {
            if($resource->getName() == $resourceName){
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getCurrentRole(): string
    {
        return $this->currentRole;
    }

    /**
     * @param string $currentRole
     * @return AccessControl
     */
    public function setCurrentRole(string $currentRole): AccessControl
    {
        $this->currentRole = $currentRole;
        return $this;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function dataTableAllowed(string $class): bool
    {
        if ($this->resourceExists($class)){
            return $this->allowed($class);
        }

        return $this->allowed(Permission::ACCESS_DATATABLES_DEFAULT);
    }
}