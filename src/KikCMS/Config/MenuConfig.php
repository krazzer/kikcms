<?php

namespace KikCMS\Config;

/**
 * Contains the default menu structure for the CMS
 */
class MenuConfig
{
    const MENU_STRUCTURE = [
        self::MENU_GROUP_CONTENT => [
            self::MENU_ITEM_MAIN_MENU,
            self::MENU_ITEM_DATA_FORM,
            self::MENU_ITEM_MEDIA,
            self::MENU_ITEM_TEMPLATES,
            self::MENU_ITEM_MENUS,
        ],
        self::MENU_GROUP_STATS   => [
            self::MENU_ITEM_STATS,
            self::MENU_ITEM_STATS_SOURCES,
        ],
        self::MENU_GROUP_CMS     => [
            self::MENU_ITEM_USERS,
            self::MENU_ITEM_LOGOUT,
        ],
    ];

    const MENU_GROUP_CONTENT = 'content';
    const MENU_GROUP_STATS   = 'stats';
    const MENU_GROUP_CMS     = 'cms';

    const MENU_ITEM_MAIN_MENU = 'menu/main';
    const MENU_ITEM_DATA_FORM = 'form';
    const MENU_ITEM_MEDIA     = 'media';
    const MENU_ITEM_TEMPLATES = 'templates';
    const MENU_ITEM_MENUS     = 'menus';

    const MENU_ITEM_STATS         = 'stats/index';
    const MENU_ITEM_STATS_SOURCES = 'stats/sources';

    const MENU_ITEM_USERS  = 'users';
    const MENU_ITEM_LOGOUT = 'logout';
}