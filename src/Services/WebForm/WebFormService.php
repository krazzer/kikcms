<?php
declare(strict_types=1);

namespace KikCMS\Services\WebForm;


use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\ElementInterface;
use Phalcon\Validation\MessageInterface;
use Phalcon\Validation\Validator\PresenceOf;
use ReflectionClass;

class WebFormService extends Injectable
{
    /**
     * @param MessageInterface $message
     * @param ElementInterface $element
     * @return bool
     */
    public function messageNeedsAlert(MessageInterface $message, ElementInterface $element): bool
    {
        if($element instanceof Hidden){
            return true;
        }

        return $message->getType() != (new ReflectionClass(PresenceOf::class))->getShortName();
    }
}