<?php
declare(strict_types=1);

namespace KikCMS\Services\WebForm;


use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Forms\Element\ElementInterface;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Messages\MessageInterface;
use Phalcon\Validation\Validator\PresenceOf;

class WebFormService extends Injectable
{
    /**
     * @param MessageInterface $message
     * @param ElementInterface $element
     * @param array $input
     * @return bool
     */
    public function messageNeedsAlert(MessageInterface $message, ElementInterface $element, array $input): bool
    {
        // never alert if the field is empty
        if(array_key_exists($element->getName(), $input) && ! $input[$element->getName()]){
            return false;
        }

        if($element instanceof Hidden){
            return true;
        }

        return $message->getType() != PresenceOf::class;
    }
}