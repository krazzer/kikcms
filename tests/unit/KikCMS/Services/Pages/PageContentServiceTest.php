<?php
declare(strict_types=1);

namespace KikCMS\Services\Pages;

use Codeception\Test\Unit;
use KikCMS\Models\Page;
use KikCmsCore\Services\DbService;
use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Di;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData\Memory;

class PageContentServiceTest extends Unit
{
    public function testGetPageVariable()
    {
        $di = new Di();

        $di->set('db', new Sqlite(["dbname" => ":memory:"]));
        $di->set('dbService', new DbService);
        $di->set('modelsManager', new Manager);
        $di->set('modelsMetadata', new Memory);

        $pageContentService = new PageContentService();
        $pageContentService->setDI($di);

        $pageContentService->db->createTable('cms_page_content', null, [
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

        $pageMock = $this->createMock(Page::class);
        $pageMock->method('getId')->willReturn(1);

        // test empty
        $this->assertNull($pageContentService->getPageVariable($pageMock, 'field'));

        // test empty
        $pageContentService->db->insert('cms_page_content', ['1', 'field', 'x'], ['page_id', 'field', 'value']);

        $this->assertEquals('x', $pageContentService->getPageVariable($pageMock, 'field'));
    }
}
