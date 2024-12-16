<?php

namespace KikCMS\Classes\WebForm\Fields;

use KikCMS\Classes\Phalcon\Validator\SpamBlock;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Config\SpamBlockConfig;
use Phalcon\Forms\Element\Text;

class SpamBlockField extends Field
{
    /**
     * @param string|null $key
     */
    public function __construct(string $key = null)
    {
        if ( ! $key) {
            $key = 'spmblkr';
        }

        $this->key = $key;
    }

    /**
     * @inheritDoc
     */
    public function setForm($form): Field|static
    {
        parent::setForm($form);

        $sessionKey = 'spamBlockField' . ucfirst($this->key) . 'SessionID';

        if ( ! $this->getForm()->request->isPost()) {
            $questionId = array_rand(SpamBlockConfig::QUESTIONS);
            $this->getForm()->session->set($sessionKey, $questionId);
        } else {
            $questionId = $this->getForm()->session->get($sessionKey);
        }

        // session is not set, so it's a spammer anyhow
        if( ! $questionId){
            $questionId = array_rand(SpamBlockConfig::QUESTIONS);
        }

        $question = $this->getForm()->translator->tl('spamBlock.' . SpamBlockConfig::QUESTIONS[$questionId]['q']);
        $question = $question . ' (' . $this->getForm()->translator->tl('spamBlock.check') . ')';

        $element = (new Text($this->key))
            ->setLabel($question)
            ->setAttribute('class', 'form-control')
            ->addValidators([new SpamBlock(['questionId' => $questionId])]);

        $this->element = $element;

        return $this;
    }
}