<?php
declare(strict_types=1);

namespace KikCMS\Services;

use Codeception\Test\Unit;
use Error;
use Phalcon\Http\Request;
use stdClass;

class ErrorServiceTest extends Unit
{
    public function testGetErrorView()
    {
        $errorService = new ErrorService();

        $requestMock = $this->createMock(Request::class);
        $requestMock->method('isAjax')->willReturn(false);

        $errorService->request = $requestMock;

        // no error
        $this->assertNull($errorService->getErrorView(null, true));

        // is recoverable and production: do nothing (array)
        $this->assertNull($errorService->getErrorView(['type' => E_WARNING], true));

        // is recoverable and production: do nothing (object)
        $error = new StdClass;
        $error->type = E_WARNING;

        $this->assertNull($errorService->getErrorView($error, true));

        // is not recoverable and dev: show error
        $this->assertEquals('500', $errorService->getErrorView(new Error, false));

        // is not recoverable and production: show error
        $this->assertEquals('500', $errorService->getErrorView(new Error, true));

        $requestMock = $this->createMock(Request::class);
        $requestMock->method('isAjax')->willReturn(true);

        $errorService->request = $requestMock;

        // is not recoverable and dev and ajax: only show content
        $this->assertEquals('500content', $errorService->getErrorView(new Error, false));

        // is not recoverable and production and ajax: show error message only
        $this->assertEquals('500', $errorService->getErrorView(new Error, true));
    }
}
