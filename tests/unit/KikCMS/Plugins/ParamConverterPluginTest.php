<?php

namespace KikCMS\Plugins;


use Exception;
use KikCMS\Controllers\FinderController;
use KikCMS\Models\File;
use KikCMS\Services\ModelService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ParamConverterPluginTest extends TestCase
{
    public function testGetConvertedParameters()
    {
        // test success
        $paramConverterPlugin = $this->getParamConverterPlugin(true);

        $methodParams = (new ReflectionMethod(FinderController::class, 'fileAction'))->getParameters();

        $result = $paramConverterPlugin->getConvertedParameters($methodParams, ['fileId' => 1]);

        $this->assertInstanceOf(File::class, $result['file']);

        // test not an object
        $methodParams = (new ReflectionMethod(TestController::class, 'noObjectParamAction'))->getParameters();

        $result = $paramConverterPlugin->getConvertedParameters($methodParams, ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $result);

        // test not a Model object
        $methodParams = (new ReflectionMethod(TestController::class, 'noModelParamAction'))->getParameters();

        $result = $paramConverterPlugin->getConvertedParameters($methodParams, ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $result);

        // test 'Id' missing from given parameters
        $methodParams = (new ReflectionMethod(FinderController::class, 'fileAction'))->getParameters();

        $result = $paramConverterPlugin->getConvertedParameters($methodParams, ['file' => 1]);

        $this->assertEquals(['file' => 1], $result);

        // test object not found
        $paramConverterPlugin = $this->getParamConverterPlugin(false);

        $methodParams = (new ReflectionMethod(FinderController::class, 'fileAction'))->getParameters();

        $this->expectException(Exception::class);

        $paramConverterPlugin->getConvertedParameters($methodParams, ['fileId' => 1]);
    }

    /**
     * @param bool $returnObject
     * @return ParamConverterPlugin
     */
    private function getParamConverterPlugin(bool $returnObject): ParamConverterPlugin
    {
        $fileMock = $this->createMock(File::class);

        $modelServiceMock = $this->createMock(ModelService::class);
        $modelServiceMock->method('getObject')->willReturn($returnObject ? $fileMock : null);

        $paramConverterPlugin = new ParamConverterPlugin();
        $paramConverterPlugin->modelService = $modelServiceMock;

        return $paramConverterPlugin;
    }
}

class TestController
{
    public function noObjectParamAction(string $test)
    {

    }

    public function noModelParamAction(\stdClass $object)
    {

    }
}