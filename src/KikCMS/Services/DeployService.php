<?php

namespace KikCMS\Services;

use KikCMS\Config\KikCMSConfig;
use Phalcon\Config;
use Phalcon\Di\Injectable;

/**
 * @property Config $config
 * @property MailService $mailService
 */
class DeployService extends Injectable
{
    public function deploy()
    {
        // You can only deploy on production!
        if ($this->config->application->env != KikCMSConfig::ENV_PROD) {
            return;
        }

        $deployCommand = 'git fetch origin && git reset --hard origin/master && composer update';

        // Execute deployment command
        exec('cd ' . dirname($_SERVER['DOCUMENT_ROOT']) . ' && ' . $deployCommand, $output);

        // Notify Webmaster
        $this->sendMessage($output);
    }

    /**
     * Send
     *
     * @param array $output
     */
    private function sendMessage($output)
    {
        $hostName    = $_SERVER['HTTP_HOST'];
        $serverName  = $_SERVER['SERVER_NAME'];
        $serverEmail = $_SERVER['SERVER_ADMIN'];

        $webmasterEmail = $this->config->application->webmasterEmail;
        $webmasterName  = $this->config->application->webmasterName;

        $subject = 'Deploy op ' . $hostName;

        $body = 'Deploy uitgevoerd op ' . $hostName . ' gaf de volgende output:' . PHP_EOL . PHP_EOL .
            implode(PHP_EOL, $output);

        $message = $this->mailService->createMessage()
            ->setSubject($subject)
            ->setFrom([$serverEmail => $serverName])
            ->setTo([$webmasterEmail => $webmasterName])
            ->setBody($body);

        // Send the message
        $this->mailService->send($message);
    }
}