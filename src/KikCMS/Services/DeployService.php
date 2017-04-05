<?php

namespace KikCMS\Services;

use KikCMS\Config\KikCMSConfig;
use Phalcon\Config;
use Phalcon\Di\Injectable;

/**
 * @property Config $applicationConfig
 * @property MailService $mailService
 */
class DeployService extends Injectable
{
    /**
     * Initiates the deploy sequence
     */
    public function deploy()
    {
        // You can only deploy on production!
        if ($this->applicationConfig->env != KikCMSConfig::ENV_PROD) {
            return;
        }

        $composerDir = $this->getRootDir() . '/../../bin/';

        $composerCommand = 'php ' . $composerDir . 'composer update ' . KikCMSConfig::PACKAGE_NAME;
        $deployCommands  = 'git fetch origin && git reset --hard origin/master && ' . $composerCommand . ' 2>&1';

        // Execute deployment command
        putenv('COMPOSER_HOME=' . $composerDir);
        exec('cd ' . $this->getRootDir() . ' && ' . $deployCommands, $output);

        $assetSymlinkExists = $this->checkAssetSymlink();

        if ( ! $assetSymlinkExists) {
            $this->createAssetSymlink();

            $output[] = 'Symlink for assets created.';
        }

        $this->checkAndCreateRequiredDirs();

        // Notify Webmaster
        $this->sendMessage($output);
    }

    /**
     * Send the deploy commands' output to the webmaster
     *
     * @param array $output
     */
    private function sendMessage($output)
    {
        $hostName    = $_SERVER['HTTP_HOST'];
        $serverName  = $_SERVER['SERVER_NAME'];
        $serverEmail = $_SERVER['SERVER_ADMIN'];

        $webmasterEmail = $this->applicationConfig->webmasterEmail;
        $webmasterName  = $this->applicationConfig->webmasterName;

        $subject = 'Deploy op ' . $hostName;
        $body    = 'Deploy uitgevoerd op ' . $hostName . " gaf de volgende output:\n\n" . $this->flattenOutput($output);

        $message = $this->mailService->createMessage()
            ->setSubject($subject)
            ->setFrom([$serverEmail => $serverName])
            ->setTo([$webmasterEmail => $webmasterName])
            ->setBody($body);

        $this->mailService->send($message);
    }

    /**
     * @param array $output
     * @return array
     */
    private function flattenOutput(array $output)
    {
        foreach ($output as &$outputLine) {
            $lineParts = explode(' ' . chr(8), $outputLine);

            foreach ($lineParts as $i => $linePart) {
                $linePart = trim($linePart, ' ' . chr(8));

                if (empty($linePart)) {
                    unset($lineParts[$i]);
                }
            }

            $outputLine = implode(PHP_EOL, $lineParts);
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * Checks whether the symlink for assets exists
     */
    private function checkAssetSymlink()
    {
        exec('cd ' . $this->getRootDir() . '/public_html/ && ls -F', $output);

        foreach ($output as $line) {
            if (strstr($line, 'cmsassets@')) {
                return true;
            }
        }

        return false;
    }

    private function createAssetSymlink()
    {
        $symlinkCommand = 'ln -s ../vendor/kiksaus/kikcms/resources/ cmsassets';

        exec('cd ' . $this->getRootDir() . '/public_html/ && ' . $symlinkCommand, $output);
    }

    /**
     * @return string
     */
    private function getRootDir()
    {
        return dirname($_SERVER['DOCUMENT_ROOT']);
    }

    /**
     * Checks if required directories exists and create them if not
     */
    private function checkAndCreateRequiredDirs()
    {
        $requiredDirs = [
            'cache',
            'cache/cache',
            'cache/metadata',
            'storage',
            'storage/media',
            'storage/thumbs',
        ];

        foreach ($requiredDirs as $dir) {
            $dirPath = SITE_PATH . $dir;

            if ( ! file_exists($dirPath)) {
                mkdir($dirPath);
            }
        }
    }
}