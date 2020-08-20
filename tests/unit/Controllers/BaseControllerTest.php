<?php
declare(strict_types=1);

namespace unit\Controllers;

use Helpers\Unit;
use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use Phalcon\Http\Response;
use Website\Controllers\TestModuleController;

class BaseControllerTest extends Unit
{
    public function testOutputFileException()
    {
        $baseController = new TestModuleController();

        $this->expectException(ObjectNotFoundException::class);

        $baseController->outputFileExceptionAction();
    }

    public function testOutputFile()
    {
        $baseController = new TestModuleController();
        $baseController->response = new Response();

        $result = $baseController->outputFileAction();

        $this->assertNotNull($result);
    }

    public function testOutputCsv()
    {
        $baseController = new TestModuleController();
        $baseController->response = new Response();

        $baseController->outputCsvAction();

        $this->expectOutputString("key1;key2\nval1;val2\n");
    }
}
