<?php

namespace KikCMS\Services;


use Phalcon\Config;
use Phalcon\Di\Injectable;
use Swift_Attachment;
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
     * @param string|array $replyTo
     * @param string|array $to
     * @param string $subject
     * @param string $body
     *
     * @param null $template
     * @param array $parameters
     * @param array $attachments
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMail($replyTo, $to, string $subject, string $body, $template = null, array $parameters = [], array $attachments = []): int
    {
        if ($template) {
            $parameters['body']    = $body;
            $parameters['subject'] = $subject;

            $body = $this->view->getPartial($template, $parameters);
        }

        $from = $this->getFrom();

        $message = $this->createMessage()
            ->setFrom($from)
            ->setReplyTo($replyTo)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        foreach ($attachments as $attachment){
            $message->attach(Swift_Attachment::fromPath($attachment));
        }

        return $this->send($message);
    }

    /**
     * Send a mail from the company's name to a user
     *
     * @param $to
     * @param string $subject
     * @param string $body
     *
     * @param array $parameters
     * @param array $attachments
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMailUser($to, string $subject, string $body, array $parameters = [], array $attachments = []): int
    {
        $companyName  = $this->config->company->name;
        $companyEmail = $this->config->company->email;

        $parameters = array_merge([
            'logo'    => $this->config->company->logoMail,
            'address' => $companyName . ', ' . $this->config->company->address,
        ], $parameters);

        if(isset($this->config->company->mainColor)){
            $parameters['mainColor'] = $this->config->company->mainColor;
        }

        if(isset($this->config->company->mainColorDark)){
            $parameters['mainColorDark'] = $this->config->company->mainColorDark;
        }

        return $this->sendMail([$companyEmail => $companyName], $to, $subject, $body, '@kikcms/mail/default', $parameters, $attachments);
    }

    /**
     * Send a service type mail, an email send from the CMS to someone using the CMS
     *
     * @param $to
     * @param string $subject
     * @param string $body
     * @param array $parameters
     * @param null|string|array $from normally
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendServiceMail($to, string $subject, string $body, array $parameters = [], $from = null): int
    {
        $developerEmail = $this->applicationConfig->developerEmail;
        $developerName  = $this->applicationConfig->developerName;

        $from = $from ?: [$developerEmail => $developerName];

        $parameters = array_merge([
            'logo'    => 'cmsassets/images/kikcms.png',
            'address' => 'Kiksaus, Heinenwaard 4, 1824 DZ Alkmaar',
        ], $parameters);

        return $this->sendMail($from, $to, $subject, $body, '@kikcms/mail/default', $parameters);
    }

    /**
     * @return array|string
     */
    private function getFrom()
    {
        if( ! $this->config->get('company') || ! $this->config->get('company')->get('email')){
            return 'noreply@' . $this->request->getServerName();
        }

        if( ! $this->config->company->get('name')){
            return $this->config->company->email;
        }

        return [$this->config->company->email => $this->config->company->name];
    }
}