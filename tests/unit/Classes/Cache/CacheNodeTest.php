<?php


namespace unit\Classes\Cache;


use KikCMS\Classes\Cache\CacheNode;
use KikCMS\ObjectLists\CacheNodeMap;
use PHPUnit\Framework\TestCase;
use stdClass;

class CacheNodeTest extends TestCase
{
    public function testGetValueOutput()
    {
        // test with subnode
        $cacheNode = new CacheNode();

        $cacheNodeMap = new CacheNodeMap();
        $cacheNodeMap->add((new CacheNode)->setValue('test'), 'test');

        $cacheNode->setCacheNodeMap($cacheNodeMap);

        $this->assertEquals('', $cacheNode->getValueOutput());

        // test no value
        $cacheNode = new CacheNode();

        $this->assertEquals('null', $cacheNode->getValueOutput());

        // test true
        $cacheNode = (new CacheNode)->setValue(true);

        $this->assertEquals('(bool) true', $cacheNode->getValueOutput());

        // test false
        $cacheNode = (new CacheNode)->setValue(false);

        $this->assertEquals('(bool) false', $cacheNode->getValueOutput());

        // test string
        $cacheNode = (new CacheNode)->setValue('somestring');

        $this->assertEquals('somestring', $cacheNode->getValueOutput());

        // test array
        $array     = ['test', 'test2'];
        $cacheNode = (new CacheNode)->setValue($array);

        $this->assertEquals(serialize($array), $cacheNode->getValueOutput());

        // test object
        $object        = new stdClass();
        $object->value = 'test';

        $cacheNode = (new CacheNode)->setValue($object);

        $this->assertEquals(serialize($object), $cacheNode->getValueOutput());
    }

    public function testGetTotal()
    {
        // test with subs (2)
        $cacheNode = (new CacheNode)->setCacheNodeMap((new CacheNodeMap)
            ->add((new CacheNode)->setValue('test1Value'), 'test1')
            ->add((new CacheNode)->setValue('test2Value'), 'test2')
        );

        $this->assertEquals(2, $cacheNode->getTotal());

        // test value (1)
        $cacheNode = (new CacheNode)->setValue('test');

        $this->assertEquals(1, $cacheNode->getTotal());

        // test with subsubs (3)
        $subCacheNodeMap = (new CacheNodeMap)
            ->add((new CacheNode)->setValue('subTest1Value'), 'subTest1')
            ->add((new CacheNode)->setValue('subTest2Value'), 'subTest2');

        $cacheNode = (new CacheNode)->setCacheNodeMap((new CacheNodeMap)
            ->add((new CacheNode)->setValue('test1Value'), 'test1')
            ->add((new CacheNode)->setCacheNodeMap($subCacheNodeMap), 'test2')
        );

        $this->assertEquals(3, $cacheNode->getTotal());
    }

    public function testFlattenSingleNodes()
    {
        $subSubSubCacheNodeMap = (new CacheNodeMap)
            ->add((new CacheNode)->setKey('subSubSub1')->setFullKey('test:sub:subSub:subSubSub1'), 'subSubSub1')
            ->add((new CacheNode)->setKey('subSubSub2')->setFullKey('test:sub:subSub:subSubSub2'), 'subSubSub2');

        $subSubCacheNode = (new CacheNode)
            ->setKey('subSub')
            ->setFullKey('test:sub:subSub')
            ->setCacheNodeMap($subSubSubCacheNodeMap);

        $subSubCacheNodeMap = (new CacheNodeMap)->add($subSubCacheNode, 'subSub');
        $subCacheNode       = (new CacheNode)->setKey('sub')->setFullKey('test:sub');

        $subCacheNode->setCacheNodeMap($subSubCacheNodeMap);

        $cacheNodeMap = (new CacheNodeMap)
            ->add($subCacheNode, 'sub');

        $cacheNode = (new CacheNode)
            ->setCacheNodeMap($cacheNodeMap)
            ->setKey('test');

        $this->assertEquals('test', $cacheNode->getKey());

        $cacheNode->flattenSingleNodes();

        $this->assertEquals('test.sub.subSub', $cacheNode->getKey());
    }

    public function testGetKey()
    {
        $cacheNode = new CacheNode();
        $cacheNode->setKey('test');

        $this->assertEquals('test', $cacheNode->getKey());
    }

    public function testSetKey()
    {
        $cacheNode = new CacheNode();

        $cacheNodeReturned = $cacheNode->setKey('test');

        $this->assertEquals('test', $cacheNode->getKey());
        $this->assertEquals($cacheNodeReturned, $cacheNode);
    }

    public function testGetFullKey()
    {
        $cacheNode = new CacheNode();
        $cacheNode->setFullKey('test:test');

        $this->assertEquals('test:test', $cacheNode->getFullKey());
    }

    public function testSetFullKey()
    {
        $cacheNode = new CacheNode();

        $cacheNodeReturned = $cacheNode->setFullKey('test:test');

        $this->assertEquals('test:test', $cacheNode->getFullKey());
        $this->assertEquals($cacheNodeReturned, $cacheNode);
    }

    public function testGetValue()
    {
        $cacheNode = new CacheNode();
        $cacheNode->setValue('test');

        $this->assertEquals('test', $cacheNode->getValue());
    }

    public function testSetValue()
    {
        $cacheNode = new CacheNode();

        $cacheNodeReturned = $cacheNode->setValue('test');

        $this->assertEquals('test', $cacheNode->getValue());
        $this->assertEquals($cacheNodeReturned, $cacheNode);
    }

    public function testSetCacheNodeMap()
    {
        $cacheNode    = new CacheNode();
        $cacheNodeMap = new CacheNodeMap();

        $cacheNodeReturned = $cacheNode->setCacheNodeMap($cacheNodeMap);

        $this->assertInstanceOf(CacheNodeMap::class, $cacheNode->getCacheNodeMap());
        $this->assertEquals($cacheNodeMap, $cacheNode->getCacheNodeMap());
        $this->assertEquals($cacheNodeReturned, $cacheNode);
    }
}