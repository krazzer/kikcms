<?php
declare(strict_types=1);

namespace unit\Services\Util;

use Helpers\Unit;
use KikCMS\Services\Util\DateTimeService;
use Phalcon\Filter\Validation\Validator\Date;

class DateTimeServiceTest extends Unit
{
    public function testStringToDateFormat()
    {
        $dateTimeService = new DateTimeService();
        $dateTimeService->setDI($this->getDbDi());

        $this->assertEquals('', $dateTimeService->stringToDateFormat(''));
        $this->assertEquals('jan 20 2010', $dateTimeService->stringToDateFormat('2010-01-20'));
        $this->assertEquals('', $dateTimeService->stringToDateTimeFormat(''));
        $this->assertEquals('jan 20 2010, at 10:10', $dateTimeService->stringToDateTimeFormat('2010-01-20 10:10:10'));
    }

    public function testGetValidator()
    {
        $dateTimeService = new DateTimeService();
        $dateTimeService->setDI($this->getDbDi());

        $this->assertInstanceOf(Date::class, $dateTimeService->getValidator());
    }

    public function testGetFromDatePickerValue()
    {
        $dateTimeService = new DateTimeService();
        $dateTimeService->setDI($this->getDbDi());

        $this->assertEquals('20-01-2020', $dateTimeService->getFromDatePickerValue('2020-01-20')->format('d-m-Y'));
    }

    public function testGetDateFormat()
    {
        $dateTimeService = new DateTimeService();
        $dateTimeService->setDI($this->getDbDi());

        $this->assertEquals('Y-m-d', $dateTimeService->getDateFormat());
    }
}
