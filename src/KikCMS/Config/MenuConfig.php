<?php

namespace KikCMS\Config;

/**
 * Contains the default menu structure for the CMS
 */
class MenuConfig
{
    const MENU_STRUCTURE = [
        self::MENU_GROUP_CONTENT => [
            self::MENU_ITEM_PAGES    => 'pages',
            self::MENU_ITEM_MEDIA    => 'media',
            self::MENU_ITEM_SETTINGS => 'settings',
        ],
        self::MENU_GROUP_STATS   => [
            self::MENU_ITEM_STATS         => 'stats/index',
            self::MENU_ITEM_STATS_SOURCES => 'stats/sources',
        ],
        self::MENU_GROUP_CMS     => [
            self::MENU_ITEM_USERS  => 'users',
            self::MENU_ITEM_LOGOUT => 'logout',
        ],
    ];

    const MENU_GROUP_CONTENT = 'content';
    const MENU_GROUP_STATS   = 'stats';
    const MENU_GROUP_CMS     = 'cms';

    const MENU_ITEM_PAGES    = 'pages';
    const MENU_ITEM_MEDIA    = 'media';
    const MENU_ITEM_PRODUCTS = 'products';
    const MENU_ITEM_SETTINGS = 'settings';

    const MENU_ITEM_STATS         = 'statsIndex';
    const MENU_ITEM_STATS_SOURCES = 'statsSources';

    const MENU_ITEM_USERS  = 'users';
    const MENU_ITEM_LOGOUT = 'logout';
}