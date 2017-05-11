<?php

namespace KikCMS\Classes;


use Phalcon\Acl;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Role;
use Phalcon\Di\Injectable;

class Permission extends Injectable
{
    const DEVELOPER = 'developer';
    const ADMIN     = 'admin';
    const USER      = 'user';
    const CLIENT    = 'client';

    const ROLES = [
        self::DEVELOPER,
        self::ADMIN,
        self::USER,
        self::CLIENT,
    ];

    /**
     * @return Memory
     */
    public function getAcl()
    {
        if (isset($this->persistent->acl)) {
            return $this->persistent->acl;
        }

        $acl = new Memory();

        $acl->setDefaultAction(Acl::DENY);

        $acl->addRole(new Role(self::DEVELOPER));
        $acl->addRole(new Role(self::ADMIN));
        $acl->addRole(new Role(self::USER));
        $acl->addRole(new Role(self::CLIENT));

        $acl->addResource(new Resource('SomeResource'), '*');
        $acl->allow(self::DEVELOPER, 'SomeResource', '*');

        $this->addDataTableAccess($acl);

        $this->persistent->acl = $acl;

        return $acl;
    }

    /**
     * @param Memory $acl
     */
    private function addDataTableAccess(Memory $acl)
    {

    }
}