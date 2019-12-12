<?php
declare(strict_types=1);

namespace Helpers;


use Exception;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Translator;
use KikCMS\Models\Language;
use KikCMS\Models\User;
use KikCMS\Services\CacheService;
use KikCMS\Services\DataTable\NestedSetService;
use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\LanguageService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\UrlService;
use KikCmsCore\Services\DbService;
use Phalcon\Config;
use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Di;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData\Memory;
use Phalcon\Security;
use Phalcon\Validation;
use Website\TestClasses\TemplateFields;
use Website\TestClasses\WebsiteSettings;

class Unit extends \Codeception\Test\Unit
{
    /** @var Di */
    private $cachedDbDi;

    public function addDefaultLanguage()
    {
        $language = new Language();

        $language->code   = 'en';
        $language->active = 1;
        $language->save();
    }

    /**
     * Get a Di with a Db class that contains a Sqlite version of the KikCMS's dedb structure
     *
     * @return Di
     */
    public function getDbDi(): Di
    {
        if($this->cachedDbDi){
            Di::setDefault($this->cachedDbDi);
            return $this->cachedDbDi;
        }

        $di = new Di();

        $db = new Sqlite(["dbname" => ":memory:"]);

        $validation = new Validation;

        $translator = new Translator();
        $translator->validation = $validation;
        $translator->setLanguageCode('en');

        $config = new Config();
        $config->application = new Config();
        $config->application->defaultLanguage = 'en';

        $di->set('db', $db);
        $di->set('config', $config);
        $di->set('dbService', new DbService);
        $di->set('security', new Security);
        $di->set('modelsManager', new Manager);
        $di->set('modelsMetadata', new Memory);
        $di->set('languageService', new LanguageService);
        $di->set('cacheService', new CacheService);
        $di->set('templateFields', new TemplateFields);
        $di->set('pageService', new PageService);
        $di->set('nestedSetService', new NestedSetService);
        $di->set('pageRearrangeService', new PageRearrangeService);
        $di->set('websiteSettings', new WebsiteSettings);
        $di->set('pageLanguageService', new PageLanguageService);
        $di->set('urlService', new UrlService);
        $di->set('validation', $validation);
        $di->set('translator', $translator);
        $di->set('cache', function (){ return null; });

        Di::setDefault($di);

        $db->createTable('cms_page_content', null, [
            'columns'    => [
                new Column('page_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('field', ['type' => Column::TYPE_VARCHAR, 'size' => 16, 'notNull' => true]),
                new Column('value', ['type' => Column::TYPE_LONGBLOB]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['page_id', 'field']),
                new Index('field', ['field']),
            ],
            'references' => [
                new Reference('cms_page_content_ibfk_1', [
                    'referencedTable'   => 'cms_page',
                    'columns'           => ['page_id'],
                    'referencedColumns' => ['id'],
                ]),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $db->createTable('cms_page_language_content', null, [
            'columns'    => [
                new Column('page_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('language_code', ['type' => Column::TYPE_VARCHAR, 'notNull' => true]),
                new Column('field', ['type' => Column::TYPE_VARCHAR, 'size' => 16, 'notNull' => true]),
                new Column('value', ['type' => Column::TYPE_LONGBLOB]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['page_id', 'language_code', 'field']),
                new Index('language_code', ['language_code']),
                new Index('field', ['field']),
            ],
            'references' => [
                new Reference('cms_page_content_ibfk_1', [
                    'referencedTable'   => 'cms_page',
                    'columns'           => ['page_id'],
                    'referencedColumns' => ['id'],
                ]),
                new Reference('cms_page_content_ibfk_2', [
                    'referencedTable'   => 'cms_language',
                    'columns'           => ['language_code'],
                    'referencedColumns' => ['id'],
                ]),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $db->createTable('cms_page_language', null, [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('page_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('language_code', ['type' => Column::TYPE_VARCHAR, 'notNull' => true]),
                new Column('active', ['type' => Column::TYPE_INTEGER, 'default' => 1]),
                new Column('name', ['type' => Column::TYPE_VARCHAR]),
                new Column('slug', ['type' => Column::TYPE_VARCHAR]),
                new Column('seo_title', ['type' => Column::TYPE_VARCHAR]),
                new Column('seo_description', ['type' => Column::TYPE_VARCHAR]),
                new Column('seo_keywords', ['type' => Column::TYPE_VARCHAR]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['id']),
                new Index('page_id', ['page_id', 'language_code'], 'UNIQUE'),
                new Index('language_code', ['language_code']),
            ],
            'references' => [
                new Reference('cms_page_language_ibfk_1', [
                    'referencedTable'   => 'cms_language',
                    'columns'           => ['language_code'],
                    'referencedColumns' => ['code'],
                ]),
                new Reference('cms_page_language_ibfk_2', [
                    'referencedTable'   => 'cms_page',
                    'columns'           => ['page_id'],
                    'referencedColumns' => ['id'],
                ]),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $db->createTable('cms_language', null, [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('code', ['type' => Column::TYPE_VARCHAR, 'size' => 3, 'notNull' => false]),
                new Column('name', ['type' => Column::TYPE_LONGBLOB]),
                new Column('active', ['type' => Column::TYPE_INTEGER, 'size' => 1, 'notNull' => true, 'default' => 1]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['id']),
                new Index('code', ['code']),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $db->createTable('cms_page', null, [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('parent_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => false]),
                new Column('alias', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => false]),
                new Column('template', ['type' => Column::TYPE_VARCHAR, 'size' => 16, 'notNull' => false]),
                new Column('display_order', ['type' => Column::TYPE_INTEGER, 'size' => 16, 'notNull' => false]),
                new Column('key', ['type' => Column::TYPE_VARCHAR, 'size' => 32, 'notNull' => false]),
                new Column('type', ['type' => Column::TYPE_VARCHAR, 'size' => 16, 'notNull' => false, 'default' => 'page']),
                new Column('level', ['type' => Column::TYPE_INTEGER, 'size' => 16, 'notNull' => false]),
                new Column('lft', ['type' => Column::TYPE_INTEGER, 'size' => 16, 'notNull' => false]),
                new Column('rgt', ['type' => Column::TYPE_INTEGER, 'size' => 16, 'notNull' => false]),
                new Column('link', ['type' => Column::TYPE_VARCHAR, 'size' => 255, 'notNull' => false]),
                new Column('menu_max_level', ['type' => Column::TYPE_INTEGER, 'size' => 16, 'notNull' => false]),
                new Column('created_at', ['type' => Column::TYPE_DATETIME, 'default' => 'now()']),
                new Column('updated_at', ['type' => Column::TYPE_DATETIME, 'default' => 'now()']),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['id']),
                new Index('parent_id', ['parent_id']),
                new Index('template_id', ['template_id']),
                new Index('alias', ['alias']),
                new Index('lft', ['lft']),
                new Index('rgt', ['rgt']),
                new Index('field', ['field']),
                new Index('display_order_parent_id', ['display_order', 'parent_id'], 'UNIQUE'),
                new Index('key', ['key'], 'UNIQUE'),
            ],
            'references' => [
                new Reference('cms_page_ibfk_1', [
                    'referencedTable'   => 'cms_page',
                    'columns'           => ['alias'],
                    'referencedColumns' => ['id'],
                ]),
                new Reference('cms_page_ibfk_2', [
                    'referencedTable'   => 'cms_page',
                    'columns'           => ['parent_id'],
                    'referencedColumns' => ['id'],
                ]),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $db->createTable('cms_user', null, [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('email', ['type' => Column::TYPE_VARCHAR, 'size' => 255, 'notNull' => true]),
                new Column('password', ['type' => Column::TYPE_VARCHAR, 'size' => 255]),
                new Column('blocked', ['type' => Column::TYPE_INTEGER, 'size' => 1, 'notNull' => true]),
                new Column('created_at', ['type' => Column::TYPE_DATETIME, 'notNull' => true, 'default' => 'NOW()']),
                new Column('role', ['type' => Column::TYPE_VARCHAR, 'size' => 16, 'notNull' => true]),
                new Column('remember_me', ['type' => Column::TYPE_BLOB]),
                new Column('settings', ['type' => Column::TYPE_BLOB]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['id']),
                new Index('role', ['role']),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $this->cachedDbDi = $di;

        return $di;
    }

    /**
     * @return User
     * @throws Exception
     */
    public function createAndSaveTestUser(): User
    {
        $user = new User();
        $user->id = 1;
        $user->email = 'test@test.com';
        $user->blocked = 0;
        $user->role = Permission::ADMIN;

        $user->save();

        return $user;
    }
}