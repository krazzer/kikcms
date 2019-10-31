<?php
declare(strict_types=1);

namespace KikCMS\Services;

use Codeception\Test\Unit;
use Error;
use stdClass;

class ErrorServiceTest extends Unit
{
    public function testGetErrorView()
    {
        $errorService = new ErrorService();

        unset($_SERVER['HTTP_X_REQUESTED_WITH']);

        // no error
        $this->assertNull($errorService->getErrorView(null, true));

        // is recoverable and production: do nothing (array)
        $this->assertNull($errorService->getErrorView(['type' => E_WARNING], true));

        // is recoverable and production: do nothing (object)
        $error = new StdClass;
        $error->type = E_WARNING;

        $this->assertNull($errorService->getErrorView($error, true));

        // is not recoverable and dev: show error
        $this->assertEquals('show500', $errorService->getErrorView(new Error, false));

        // is not recoverable and production: show error
        $this->assertEquals('show500', $errorService->getErrorView(new Error, true));

        // is not recoverable and dev and ajax: only show content
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $this->assertEquals('error500content', $errorService->getErrorView(new Error, false));

        // is not recoverable and production and ajax: show error message only
        $this->assertEquals('show500', $errorService->getErrorView(new Error, true));
    }
}
