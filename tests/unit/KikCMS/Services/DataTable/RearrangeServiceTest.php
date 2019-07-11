<?php

namespace KikCMS\Services\DataTable;

use Exception;
use Helpers\TestHelper;
use Helpers\Models\Person;
use Phalcon\Mvc\Model\Query\Builder;
use PHPUnit\Framework\TestCase;

class RearrangeServiceTest extends TestCase
{
    public function testMakeRoomForFirst()
    {
        $di = (new TestHelper)->getTestDi();

        $rearrangeService = new RearrangeService();
        $rearrangeService->setDI($di);

        $rearrangeService->dbService->truncate(Person::class);
        $rearrangeService->dbService->insertBulk(Person::class, [
            ['id' => 1, 'name' => 1, 'display_order' => 1],
            ['id' => 2, 'name' => 2, 'display_order' => 2],
        ]);

        $rearrangeService->makeRoomForFirst(Person::class);

        $query = (new Builder)->from(Person::class)->columns('display_order');

        $this->assertEquals([2, 3], $rearrangeService->dbService->getValues($query));

        $rearrangeService->dbService->truncate(Person::class);
    }

    public function testRearrange()
    {
        $di = (new TestHelper)->getTestDi();

        $rearrangeService = new RearrangeService();
        $rearrangeService->setDI($di);

        // test placing after
        $rearrangeService->dbService->truncate(Person::class);
        $rearrangeService->dbService->insertBulk(Person::class, [
            ['id' => 1, 'name' => 1, 'display_order' => 1],
            ['id' => 2, 'name' => 2, 'display_order' => 2],
            ['id' => 3, 'name' => 3, 'display_order' => 3],
            ['id' => 4, 'name' => 4, 'display_order' => 4],
            ['id' => 5, 'name' => 5, 'display_order' => 5],
        ]);

        $source = Person::getById(2);
        $target = Person::getById(4);

        $rearrangeService->rearrange($source, $target, RearrangeService::REARRANGE_AFTER);

        $query = (new Builder)->from(Person::class)->columns(['id', 'display_order']);

        $this->assertEquals([1 => 1, 2 => 4, 3 => 2, 4 => 3, 5 => 5], $rearrangeService->dbService->getAssoc($query));

        // test placing before
        $rearrangeService->dbService->truncate(Person::class);
        $rearrangeService->dbService->insertBulk(Person::class, [
            ['id' => 1, 'name' => 1, 'display_order' => 1],
            ['id' => 2, 'name' => 2, 'display_order' => 2],
            ['id' => 3, 'name' => 3, 'display_order' => 3],
            ['id' => 4, 'name' => 4, 'display_order' => 4],
            ['id' => 5, 'name' => 5, 'display_order' => 5],
        ]);

        $source = Person::getById(4);
        $target = Person::getById(2);

        $rearrangeService->rearrange($source, $target, RearrangeService::REARRANGE_BEFORE);

        $query = (new Builder)->from(Person::class)->columns(['id', 'display_order']);

        $this->assertEquals([1 => 1, 2 => 3, 3 => 4, 4 => 2, 5 => 5], $rearrangeService->dbService->getAssoc($query));

        $rearrangeService->dbService->truncate(Person::class);

        $rearrangeService->dbService->insertBulk(Person::class, [
            ['id' => 1, 'name' => 1, 'display_order' => 1],
            ['id' => 2, 'name' => 2, 'display_order' => null],
            ['id' => 3, 'name' => 3, 'display_order' => 2],
            ['id' => 4, 'name' => 4, 'display_order' => null],
            ['id' => 5, 'name' => 5, 'display_order' => 3],
        ]);

        $source = Person::getById(2); // will be 4
        $target = Person::getById(4); // will be 5

        $rearrangeService->rearrange($source, $target, RearrangeService::REARRANGE_BEFORE);

        $query = (new Builder)->from(Person::class)->columns(['id', 'display_order']);

        $this->assertEquals([1 => 1, 2 => 4, 3 => 2, 4 => 5, 5 => 3], $rearrangeService->dbService->getAssoc($query));

        // clean up
        $rearrangeService->dbService->truncate(Person::class);

        // exception
        $source->nonExistingField = 1;
        $target->nonExistingField = 2;

        $this->expectException(Exception::class);
        $rearrangeService->rearrange($source, $target, RearrangeService::REARRANGE_BEFORE, 'nonExistingField');
    }
}
