<?php

namespace KikCMS\Classes\Phalcon\Validator;

use KikCMS\Config\SpamBlockConfig;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Messages\Message;

class SpamBlock extends AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function validate(Validation $validation, $field): bool
    {
        $answer     = $validation->getValue($field);
        $questionId = $this->getOption('questionId');

        $correctAnswer = $validation->translator->tl('spamBlock.' . SpamBlockConfig::QUESTIONS[$questionId]['a']);
        $question      = $validation->translator->tl('spamBlock.' . SpamBlockConfig::QUESTIONS[$questionId]['q']);

        if ($correctAnswer == strtolower($answer)) {
            return true;
        }

        $answer = substr(str_pad($answer, 20, '_'), 0, 20);

        error_log(date('Y-m-d H:i:s') . ' | ' . $answer . ' | ' . $correctAnswer . ' | ' . $question . PHP_EOL, 3,
            $validation->config->application->path . 'storage/spamblock.log');

        $message = $this->getOption('message') ?: $validation->translator->tl('spamBlock.message');

        $validation->appendMessage(new Message($message, $field));
        return false;
    }
}