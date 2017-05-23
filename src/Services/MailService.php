<?php

namespace KikCMS\Services;


use Phalcon\Config;
use Phalcon\Di\Injectable;
use Swift_Mailer;
use Swift_Message;
use Swift_Mime_MimePart;

/**
 * Service for sending various mails
 *
 * @property Config $applicationConfig
 */
class MailService extends Injectable
{
    /** @var Swift_Mailer */
    private $mailer;

    /**
     * MailService constructor.
     * @param Swift_Mailer $mailer
     */
    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param Swift_Message|Swift_Mime_MimePart $message
     *
     * @return int
     */
    public function send(Swift_Message $message): int
    {
        return $this->mailer->send($message);
    }

    /**
     * @return Swift_Message
     */
    public function createMessage()
    {
        return Swift_Message::newInstance();
    }

    /**
     * @param string|array $from
     * @param string|array $to
     * @param string $subject
     * @param string $body
     *
     * @param null $template
     * @param array $parameters
     *
     * @return int
     */
    public function sendMail($from, $to, string $subject, string $body, $template = null, array $parameters = []): int
    {
        if ($template) {
            $parameters['body']    = $body;
            $parameters['subject'] = $subject;

            $body = $this->view->getPartial($template, $parameters);
        }

        $message = $this->createMessage()
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        return $this->send($message);
    }

    /**
     * Send a service type mail
     *
     * @param $to
     * @param string $subject
     * @param string $body
     * @param array $parameters
     *
     * @return int
     */
    public function sendServiceMail($to, string $subject, string $body, array $parameters = []): int
    {
        $developerEmail = $this->applicationConfig->developerEmail;
        $developerName  = $this->applicationConfig->developerName;

        return $this->sendMail([$developerEmail => $developerName], $to, $subject, $body, '@kikcms/mail/default', $parameters);
    }
}