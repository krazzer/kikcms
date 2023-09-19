<?php declare(strict_types=1);

namespace KikCMS\Classes\Exceptions;


use Exception;

class UnauthorizedException extends Exception
{
    protected $message = 'You are not allowed to view this content';
}