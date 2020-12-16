<?php


namespace KikCMS\Classes\Exceptions;


use Exception;

class DatabaseConnectionException extends Exception
{
    protected $message = 'Could not establish a connection to the database';
}