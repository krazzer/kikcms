<?php declare(strict_types=1);

namespace KikCMS\Classes\Exceptions;


class UnauthorizedException extends \Exception
{
    protected $message = 'You are not allowed to view this content';
}