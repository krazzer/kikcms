<?php
declare(strict_types=1);

namespace Helpers;


use Exception;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\KeyValue;
use KikCMS\Classes\Phalcon\PdoDialect\Sqlite;
use KikCMS\Classes\Phalcon\Storage\Adapter\Stream;
use KikCMS\Models\Language;
use KikCMS\Models\User;
use KikCMS\Services\CacheService;
use KikCMS\Services\DataTable\NestedSetService;
use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\DataTable\PagesDataTableService;
use KikCMS\Services\DataTable\RearrangeService;
use KikCMS\Services\LanguageService;
use KikCMS\Services\ModelService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\TemplateService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\TranslationService;
use KikCMS\Services\WebForm\RelationKeyService;
use KikCMS\Services\WebForm\StorageService;
use KikCMS\Services\Website\MailFormService;
use KikCmsCore\Services\DbService;
use Phalcon\Cache\Adapter\Memory as MemoryCache;
use Phalcon\Config\Config;
use Phalcon\Db\Adapter\PdoFactory;
use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Di\Di;
use Phalcon\Encryption\Security;
use Phalcon\Filter\Validation;
use Phalcon\Flash\Direct;
use Phalcon\Html\Escaper;
use Phalcon\Http\Request;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData\Memory;
use Phalcon\Storage\SerializerFactory;
use ReflectionClass;
use Website\TestClasses\TemplateFields;
use Website\TestClasses\WebsiteSettings;

class Unit extends \Codeception\Test\Unit
{
    /** @var Di */
    private $cachedDbDi;

    public function addDefaultLanguage()
    {
        $this->addLanguage();
    }

    /**
     * @param string $langCode
     */
    public function addLanguage(string $langCode = 'en')
    {
        $language = new Language();

        $language->code   = $langCode;
        $language->active = 1;
        $language->name   = 'lang';
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

        $db = (new PdoFactory)->load(['adapter' => 'sqlite', 'options' => [
            "dbname"       => ":memory:",
            'dialectClass' => new Sqlite()
        ]]);

        $translator = (new TestHelper)->getTranslator();

        $config = new Config();
        $config->application = new Config();
        $config->application->defaultLanguage = 'en';

        $adapter = new Stream(new SerializerFactory, [
            'storageDir' => (new TestHelper)->getSitePath() . 'storage/keyvalue/'
        ]);

        $keyValue = new KeyValue($adapter);
        $memoryCache = new MemoryCache(new SerializerFactory(), ['defaultSerializer' => 'Php', 'lifetime' => 300]);

        $di->set('db', $db);
        $di->set('config', $config);
        $di->set('cacheService', new CacheService);
        $di->set('dbService', new DbService);
        $di->set('security', new Security);
        $di->set('modelsManager', new Manager);
        $di->set('modelsMetadata', new Memory);
        $di->set('languageService', new LanguageService);
        $di->set('templateFields', new TemplateFields);
        $di->set('pageService', new PageService);
        $di->set('nestedSetService', new NestedSetService);
        $di->set('pageRearrangeService', new PageRearrangeService);
        $di->set('websiteSettings', new WebsiteSettings);
        $di->set('pageLanguageService', new PageLanguageService);
        $di->set('urlService', new UrlService);
        $di->set('validation', new Validation);
        $di->set('storageService', new StorageService);
        $di->set('relationKeyService', new RelationKeyService);
        $di->set('flash', new Direct);
        $di->set('escaper', new Escaper);
        $di->set('modelService', new ModelService);
        $di->set('translationService', new TranslationService);
        $di->set('rearrangeService', new RearrangeService);
        $di->set('pagesDataTableService', new PagesDataTableService);
        $di->set('templateService', new TemplateService);
        $di->set('request', new Request);
        $di->set('mailFormService', new MailFormService);
        $di->set('cache', $memoryCache);
        $di->set('translator', $translator);
        $di->set('keyValue', $keyValue);

        Di::setDefault($di);

        $db->createTable('cms_page_content', '', [
            'columns'    => [
                new Column('page_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('field', ['type' => Column::TYPE_VARCHAR, 'size' => 16, 'notNull' => true]),
                new Column('value', ['type' => Column::TYPE_LONGBLOB, 'notNull' => false]),
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

        $db->createTable('cms_page_language_content', '', [
            'columns'    => [
                new Column('page_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('language_code', ['type' => Column::TYPE_VARCHAR, 'notNull' => true]),
                new Column('field', ['type' => Column::TYPE_VARCHAR, 'size' => 16, 'notNull' => true]),
                new Column('value', ['type' => Column::TYPE_LONGBLOB, 'notNull' => false]),
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

        $db->createTable('cms_page_language', '', [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true, 'autoIncrement' => true]),
                new Column('page_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('language_code', ['type' => Column::TYPE_VARCHAR, 'notNull' => true]),
                new Column('active', ['type' => Column::TYPE_INTEGER, 'default' => 1]),
                new Column('name', ['type' => Column::TYPE_VARCHAR, 'notNull' => false]),
                new Column('slug', ['type' => Column::TYPE_VARCHAR, 'notNull' => false]),
                new Column('seo_title', ['type' => Column::TYPE_VARCHAR, 'notNull' => false]),
                new Column('seo_description', ['type' => Column::TYPE_VARCHAR, 'notNull' => false]),
                new Column('seo_keywords', ['type' => Column::TYPE_VARCHAR, 'notNull' => false]),
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

        $db->createTable('cms_language', '', [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('code', ['type' => Column::TYPE_VARCHAR, 'size' => 3, 'notNull' => false]),
                new Column('name', ['type' => Column::TYPE_LONGBLOB, 'notNull' => false]),
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

        $db->createTable('cms_page', '', [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true, 'autoIncrement' => true]),
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

        $db->createTable('cms_user', '', [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('email', ['type' => Column::TYPE_VARCHAR, 'size' => 255, 'notNull' => true]),
                new Column('password', ['type' => Column::TYPE_VARCHAR, 'size' => 255, 'notNull' => false]),
                new Column('blocked', ['type' => Column::TYPE_INTEGER, 'size' => 1, 'notNull' => true]),
                new Column('created_at', ['type' => Column::TYPE_DATETIME, 'notNull' => true, 'default' => 'NOW()']),
                new Column('role', ['type' => Column::TYPE_VARCHAR, 'size' => 16, 'notNull' => true]),
                new Column('remember_me', ['type' => Column::TYPE_BLOB, 'notNull' => false]),
                new Column('settings', ['type' => Column::TYPE_BLOB, 'notNull' => false]),
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

        $db->createTable('cms_file', '', [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('name', ['type' => Column::TYPE_VARCHAR, 'size' => 100, 'notNull' => false]),
                new Column('extension', ['type' => Column::TYPE_VARCHAR, 'size' => 50, 'notNull' => false]),
                new Column('mimetype', ['type' => Column::TYPE_VARCHAR, 'size' => 100, 'notNull' => false]),
                new Column('created', ['type' => Column::TYPE_DATETIME, 'notNull' => false]),
                new Column('updated', ['type' => Column::TYPE_DATETIME, 'notNull' => false]),
                new Column('is_folder', ['type' => Column::TYPE_INTEGER, 'notNull' => false]),
                new Column('folder_id', ['type' => Column::TYPE_INTEGER, 'notNull' => false]),
                new Column('size', ['type' => Column::TYPE_INTEGER, 'notNull' => false]),
                new Column('user_id', ['type' => Column::TYPE_INTEGER, 'notNull' => false]),
                new Column('key', ['type' => Column::TYPE_VARCHAR, 'notNull' => false]),
                new Column('hash', ['type' => Column::TYPE_VARCHAR, 'notNull' => false]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['id']),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $db->createTable('test_company', '', [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('name', ['type' => Column::TYPE_VARCHAR, 'size' => 255, 'notNull' => false]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['id']),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $db->createTable('test_company_type', '', [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('name', ['type' => Column::TYPE_VARCHAR, 'size' => 255, 'notNull' => false]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['id']),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $db->createTable('test_person', '', [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('name', ['type' => Column::TYPE_VARCHAR, 'size' => 255, 'notNull' => false]),
                new Column('company_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => false]),
                new Column('image_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => false]),
                new Column('display_order', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => false]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['id']),
                new Index('company_id', ['company_id']),
                new Index('image_id', ['image_id']),
                new Index('display_order', ['display_order'], 'UNIQUE'),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $db->createTable('test_person_interest', '', [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('person_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => false]),
                new Column('interest_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => false]),
                new Column('grade', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => false]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['id']),
                new Index('interest_id', ['interest_id']),
                new Index('person_id_interest_id', ['person_id', 'interest_id'], 'UNIQUE'),
            ],
            'references' => [
                new Reference('test_person_interest_ibfk_1', [
                    'referencedTable'   => 'test_person',
                    'columns'           => ['person_id'],
                    'referencedColumns' => ['id'],
                ]),
                new Reference('test_person_interest_ibfk_2', [
                    'referencedTable'   => 'test_interest',
                    'columns'           => ['interest_id'],
                    'referencedColumns' => ['id'],
                ]),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $db->createTable('cms_translation_value', '', [
            'columns'    => [
                new Column('key_id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('language_code', ['type' => Column::TYPE_VARCHAR, 'size' => 3, 'notNull' => false]),
                new Column('value', ['type' => Column::TYPE_LONGBLOB, 'notNull' => false]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['key_id']),
                new Index('language_code', ['language_code']),
            ],
            'references' => [
                new Reference('test_person_interest_ibfk_1', [
                    'referencedTable'   => 'cms_language',
                    'columns'           => ['language_code'],
                    'referencedColumns' => ['code'],
                ]),
                new Reference('test_person_interest_ibfk_2', [
                    'referencedTable'   => 'cms_translation_key',
                    'columns'           => ['key_id'],
                    'referencedColumns' => ['id'],
                ]),
            ],
            'options'    => [
                'ENGINE'          => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
                'CHARSET'         => 'utf8',
            ],
        ]);

        $db->createTable('cms_translation_key', '', [
            'columns'    => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'size' => 11, 'notNull' => true]),
                new Column('key', ['type' => Column::TYPE_VARCHAR, 'size' => 3, 'notNull' => false]),
                new Column('db', ['type' => Column::TYPE_INTEGER, 'size' => 1, 'notNull' => false]),
            ],
            'indexes'    => [
                new Index('PRIMARY', ['id']),
                new Index('key', ['key']),
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

    /**
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array()): mixed
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}