<?php

namespace KikCMS\Classes\Exceptions;


class UnauthorizedException extends \Exception
{
    protected $message = 'You are not allowed to view this content';
}