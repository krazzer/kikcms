<?php

namespace KikCMS\Classes;


use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\DataTables\Languages;
use KikCMS\DataTables\Templates;
use KikCMS\Services\UserService;
use Phalcon\Acl;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Role;
use Phalcon\Di\Injectable;

/**
 * @property UserService $userService
 */
class Permission extends Injectable
{
    const DEVELOPER = 'developer';
    const ADMIN     = 'admin';
    const USER      = 'user';
    const CLIENT    = 'client';
    const VISITOR   = 'visitor';

    const ACCESS_DATATABLES = 'AccessDataTables';
    const EDIT_MENUS = 'editMenus';

    const ROLES = [
        self::DEVELOPER,
        self::ADMIN,
        self::USER,
        self::CLIENT,
        self::VISITOR,
    ];

    /**
     * @return AccessControl
     */
    public function getAcl()
    {
        if (isset($this->persistent->acl)) {
            return $this->persistent->acl;
        }

        $acl = new AccessControl($this->getCurrentRole());

        $acl->setDefaultAction(Acl::DENY);

        $acl->addRole(new Role(self::DEVELOPER));
        $acl->addRole(new Role(self::ADMIN));
        $acl->addRole(new Role(self::USER));
        $acl->addRole(new Role(self::CLIENT));

        $this->addDataTablePermissions($acl);
        $this->addMenuPermissions($acl);

        $this->persistent->acl = $acl;

        return $acl;
    }

    /**
     * Get the role of the current logged in user, if not logged in, the role is visitor
     *
     * @return string
     */
    public function getCurrentRole(): string
    {
        $role = $this->session->get('role');

        if ( ! $role) {
            return Permission::VISITOR;
        }

        return $role;
    }

    /**
     * @param AccessControl $acl
     */
    private function addDataTablePermissions(AccessControl $acl)
    {
        $acl->addResource(new Resource(self::ACCESS_DATATABLES), '*');

        $acl->addResource(Languages::class, '*');
        $acl->addResource(Templates::class, '*');

        $acl->allow(self::DEVELOPER, self::ACCESS_DATATABLES, '*');
        $acl->allow(self::ADMIN, self::ACCESS_DATATABLES, '*');
        $acl->allow(self::USER, self::ACCESS_DATATABLES, '*');
        $acl->allow(self::CLIENT, self::ACCESS_DATATABLES, '*');

        $acl->allow(self::DEVELOPER, Templates::class, '*');
        $acl->allow(self::DEVELOPER, Languages::class, '*');
    }

    /**
     * @param AccessControl $acl
     */
    private function addMenuPermissions(AccessControl $acl)
    {
        $acl->addResource(new Resource(self::EDIT_MENUS), '*');

        $acl->allow(self::DEVELOPER, self::EDIT_MENUS, '*');
    }
}