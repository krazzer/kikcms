<?php

namespace KikCMS\Classes\Exceptions;


use Exception;

class ParentRelationKeyReferenceMissingException extends Exception
{
    /**
     * @param string $key
     * @param string $formClass
     */
    public function __construct(string $key, string $formClass)
    {
        $message = "Column '" . $key . "' doesn't exist in " . $formClass .
            ". It should be present in the Model's columns or a field column of the DataForm";

        parent::__construct($message);
    }
}