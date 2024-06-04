<?php


namespace unit\Classes\DataTable\Filter;


use Helpers\TestHelper;
use KikCMS\Classes\DataTable\Filter\Filter;
use Phalcon\Mvc\Model\Query\Builder;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testGetFieldWithAlias()
    {
        $filter = new TestableFilter();
        $filter->setField('test');
        $filter->setAlias('t');

        $this->assertEquals('t.[test]', $filter->getFieldWithAlias());

        $filter = new TestableFilter();
        $filter->setField('test');

        $this->assertEquals('[test]', $filter->getFieldWithAlias());
    }

    public function testGettersAndSetters()
    {
        $simpleGetterSetterTester = new TestHelper();

        $simpleGetterSetterTester->testGetterAndSetter(TestableFilter::class, ['alias', 'label', 'field', 'default']);
    }
}

class TestableFilter extends Filter
{
    public function applyFilter(Builder $builder, $value)
    {
        // nothing needed...
    }
}