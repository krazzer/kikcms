<?php declare(strict_types=1);

namespace KikCMS\Services;


use Exception;
use KikCMS\Classes\Phalcon\IniConfig;
use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Config\Config;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_Mime_MimePart;

/**
 * Service for sending various mails
 *
 * @property IniConfig $config
 * @property PlaceholderService $placeholderService
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
    public function send(Swift_Message|Swift_Mime_MimePart $message): int
    {
        return $this->mailer->send($message);
    }

    /**
     * @return Swift_Message
     */
    public function createMessage(): Swift_Message
    {
        return new Swift_Message();
    }

    /**
     * @return string|array
     */
    public function getDefaultFrom(): array|string
    {
        $defaultFromEmail = $this->getDefaultFromEmail();
        $defaultFromName  = $this->getDefaultFromName();

        if ($defaultFromName) {
            return [$defaultFromEmail => $defaultFromName];
        }

        return $defaultFromEmail;
    }

    /**
     * @return string
     */
    public function getDefaultFromEmail(): string
    {
        if ($email = $this->config->application->get('defaultFromEmail')) {
            return $email;
        }

        if ($this->config->get('company') && ($email = $this->config->get('company')->get('email'))) {
            return $email;
        }

        if ( ! $domain = $this->config->application->get('domain')) {
            if ( ! @$this->request) {
                throw new Exception('Domain to send from is unknown. Please set the application.domain setting');
            } else {
                $domain = $this->request->getServerName();
            }
        }

        return 'noreply@' . $domain;
    }

    /**
     * @return string|null
     */
    public function getDefaultFromName(): ?string
    {
        if ($name = $this->config->application->get('defaultFromName')) {
            return $name;
        }

        if ($this->config->get('company') && ($name = $this->config->get('company')->get('name'))) {
            return $name;
        }

        return null;
    }

    /**
     * @return Swift_Mailer
     */
    public function getMailer(): Swift_Mailer
    {
        return $this->mailer;
    }

    /**
     * @param array|string $to
     * @param string $subject
     * @param string $body
     *
     * @param null $template
     * @param array $parameters
     * @param array|Swift_Attachment $attachments
     * @param array|string|null $from
     * @param bool $bcc
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMail(array|string $to, string $subject, string $body, $template = null, array $parameters = [],
                             array|Swift_Attachment $attachments = [], array|string $from = null,
                             bool $bcc = false): int
    {
        if ($template) {
            $parameters['body']    = $body;
            $parameters['subject'] = $subject;

            $htmlBody = $this->view->getPartial($template, $parameters);
        } else {
            $htmlBody = $body;
        }

        $from = $from ?: $this->getDefaultFrom();

        $htmlBody = $this->placeholderService->replaceAll($htmlBody);

        $message = $this->createMessage()
            ->setFrom($from)
            ->setSubject($subject)
            ->setBody($htmlBody, 'text/html');

        if($bcc){
            $message->setBcc($to);
        } else {
            $message->setTo($to);
        }

        if ($plainTextBody = $parameters['plainTextBody'] ?? null) {
            $message->addPart($plainTextBody, 'text/plain');
        } else {
            $message->addPart(strip_tags($body), 'text/plain');
        }

        foreach ($attachments as $attachment) {
            if ($attachment instanceof Swift_Attachment) {
                $message->attach($attachment);
            } else {
                $message->attach(Swift_Attachment::fromPath($attachment));
            }
        }

        $mailsSend = $this->send($message);

        $this->eventsManager->fire('email:send', $this, ['message' => $message, 'send' => $mailsSend]);

        return $mailsSend;
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
     * @param array|string|null $from
     * @param bool $bcc
     * @return int The number of successful recipients. Can be 0 which indicates failure
     * @throws Exception
     */
    public function sendMailUser($to, string $subject, string $body, array $parameters = [], array $attachments = [],
                                 array|string $from = null, bool $bcc = false): int
    {
        $parameters = $this->updateParametersWithCompanyData($parameters, $this->config->company);

        return $this->sendMail($to, $subject, $body, '@kikcms/mail/default', $parameters, $attachments, $from, $bcc);
    }

    /**
     * Send a service type mail, an email send from the CMS to someone using the CMS
     *
     * @param $to
     * @param string $subject
     * @param string $body
     * @param array $parameters
     * @param array $attachments
     * @param array|string|null $from
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendServiceMail($to, string $subject, string $body, array $parameters = [], array $attachments = [],
                                    array|string $from = null): int
    {
        $parameters = $this->updateParametersWithCompanyData($parameters, $this->config->developer);

        $parameters['hideBranding'] = $this->config->application->hideServiceMailBranding;

        return $this->sendMail($to, $subject, $body, '@kikcms/mail/default', $parameters, $attachments, $from);
    }

    /**
     * Update the e-mail template's parameters with data for the company's appearance for the e-mail
     *
     * @param array $parameters
     * @param Config $config
     * @return array
     */
    private function updateParametersWithCompanyData(array $parameters, Config $config): array
    {
        if($config->address) {
            $addressLine = implode(', ', [$config->name, $config->address, $config->zip, $config->city]);
        } else {
            $addressLine = $config->name;
        }

        $parameters = array_merge([
            'logo'    => $config->logoMail,
            'address' => $addressLine,
        ], $parameters);

        if (isset($config->mainColor)) {
            $parameters['mainColor'] = $config->mainColor;
        }

        if (isset($config->mainColorDark)) {
            $parameters['mainColorDark'] = $config->mainColorDark;
        }

        return $parameters;
    }
}