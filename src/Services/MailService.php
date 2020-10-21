<?php declare(strict_types=1);

namespace KikCMS\Services;


use Exception;
use KikCMS\Classes\Phalcon\IniConfig;
use Phalcon\Config;
use KikCMS\Classes\Phalcon\Injectable;
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
    public function send(Swift_Message $message): int
    {
        return $this->mailer->send($message);
    }

    /**
     * @return Swift_Message
     */
    public function createMessage()
    {
        return new Swift_Message();
    }

    /**
     * @return Swift_Mailer
     */
    public function getMailer(): Swift_Mailer
    {
        return $this->mailer;
    }

    /**
     * @param string|array $to
     * @param string $subject
     * @param string $body
     *
     * @param null $template
     * @param array $parameters
     * @param array|Swift_Attachment $attachments
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMail($to, string $subject, string $body, $template = null, array $parameters = [], array $attachments = []): int
    {
        if ($template) {
            $parameters['body']    = $body;
            $parameters['subject'] = $subject;

            $htmlBody = $this->view->getPartial($template, $parameters);
        } else {
            $htmlBody = $body;
        }

        $from = $this->getDefaultFrom();

        $htmlBody = $this->placeholderService->replaceAll($htmlBody);

        $message = $this->createMessage()
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($htmlBody, 'text/html');

        if($plainTextBody = $parameters['plainTextBody'] ?? null){
            $message->addPart($plainTextBody, 'text/plain');
        } else {
            $message->addPart(strip_tags($body), 'text/plain');
        }

        foreach ($attachments as $attachment) {
            if($attachment instanceof Swift_Attachment){
                $message->attach($attachment);
            } else {
                $message->attach(Swift_Attachment::fromPath($attachment));
            }
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
        $parameters = $this->updateParametersWithCompanyData($parameters, $this->config->company);

        return $this->sendMail($to, $subject, $body, '@kikcms/mail/default', $parameters, $attachments);
    }

    /**
     * Send a service type mail, an email send from the CMS to someone using the CMS
     *
     * @param $to
     * @param string $subject
     * @param string $body
     * @param array $parameters
     *
     * @param array $attachments
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendServiceMail($to, string $subject, string $body, array $parameters = [], array $attachments = []): int
    {
        $parameters = $this->updateParametersWithCompanyData($parameters, $this->config->developer);

        return $this->sendMail($to, $subject, $body, '@kikcms/mail/default', $parameters, $attachments);
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
        $parameters = array_merge([
            'logo'    => $config->logoMail,
            'address' => $config->name . ', ' . $config->address,
        ], $parameters);

        if (isset($config->mainColor)) {
            $parameters['mainColor'] = $config->mainColor;
        }

        if (isset($config->mainColorDark)) {
            $parameters['mainColorDark'] = $config->mainColorDark;
        }

        return $parameters;
    }

    /**
     * @return string|array
     */
    private function getDefaultFrom()
    {
        if ($defaultFromEmail = $this->config->application->get('defaultFromEmail')) {
            return $defaultFromEmail;
        }

        if ( ! $domain = $this->config->application->get('domain')) {
            if ( ! @$this->request) {
                throw new Exception('Domain to send from is unknown. Please set the application.domain setting');
            } else {
                $domain = $this->request->getServerName();
            }
        }

        $from = 'noreply@' . $domain;

        if ($this->config->get('company') && $companyName = $this->config->get('company')->get('name')) {
            return [$from => $companyName];
        }

        return $from;
    }
}