<?php


namespace KikCMS\Classes\DataTable\Filter;


use Phalcon\Mvc\Model\Query\Builder;
use PHPUnit\Framework\TestCase;

class FilterSelectTest extends TestCase
{
    public function testConstruct()
    {
        $filterSelect = new FilterSelect('test', 'test', [1 => 'test'], 't');

        $this->assertEquals('test', $filterSelect->getField());
        $this->assertEquals('test', $filterSelect->getLabel());
        $this->assertEquals('t', $filterSelect->getAlias());
        $this->assertEquals([1 => 'test'], $filterSelect->getOptions());
    }

    public function testSetOptions()
    {
        $filterSelect = new FilterSelect('test', 'test', [1 => 'test'], 't');

        $filterSelectReturned = $filterSelect->setOptions([2 => 'test']);

        $this->assertEquals([2 => 'test'], $filterSelect->getOptions());
        $this->assertInstanceOf(FilterSelect::class, $filterSelectReturned);
    }

    public function testApplyFilter()
    {
        $filterSelect = new FilterSelect('test', 'test', [1 => 'test'], 't');

        $query = (new Builder);

        $filterSelect->applyFilter($query, '1');

        $this->assertStringStartsWith('t.test = :filter', $query->getWhere());
    }
}