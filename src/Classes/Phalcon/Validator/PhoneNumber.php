<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon\Validator;


use Phalcon\Validation\Validator\Regex;

class PhoneNumber extends Regex
{
    /**
     * @inheritdoc
     */
    public function __construct(array $options = null)
    {
        $options['pattern'] = '/^[0-9\-\(\)\/\+\s]*$/';

        parent::__construct($options);
    }
}