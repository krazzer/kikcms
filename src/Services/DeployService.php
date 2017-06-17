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

        $composerCommand = $this->getComposerCommand();
        $deployCommands  = 'git fetch origin && git reset --hard origin/master && ' . $composerCommand . ' 2>&1';

        // Execute deployment command
        putenv('COMPOSER_HOME=' . $this->getComposerDir());
        exec('cd ' . $this->getRootDir() . ' && ' . $deployCommands, $output);

        $assetSymlinkExists = $this->checkAssetSymlink();

        if ( ! $assetSymlinkExists) {
            $this->createAssetSymlink();
            $output[] = 'Symlink for assets created.';
        }

        $this->checkAndCreateRequiredDirs();
        $this->removeCache();

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

        $developerEmail = $this->applicationConfig->developerEmail;
        $developerName  = $this->applicationConfig->developerName;

        $subject = 'Deploy op ' . $hostName;
        $body    = 'Deploy uitgevoerd op ' . $hostName . " gaf de volgende output:\n\n" . $this->flattenOutput($output);

        $message = $this->mailService->createMessage()
            ->setSubject($subject)
            ->setFrom([$serverEmail => $serverName])
            ->setTo([$developerEmail => $developerName])
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

    /**
     * @return string
     */
    private function getComposerCommand(): string
    {
        $lockFileIsUpdated = $this->lockFileIsUpdated();
        $composerDir       = $this->getComposerDir();

        if ($lockFileIsUpdated) {
            return 'php ' . $composerDir . 'composer install && php ' . $composerDir . 'composer update kiksaus/*';
        } else {
            return 'php ' . $composerDir . 'composer update kiksaus/*';
        }
    }

    /**
     * @return string
     */
    private function getComposerDir(): string
    {
        return $this->getRootDir() . '/../../bin/';
    }

    /**
     * @return array
     */
    private function getTwoLatestCommits(): array
    {
        exec('git log -n 2 --pretty=format:"%H"', $output);
        return $output;
    }

    /**
     * @return bool
     */
    private function lockFileIsUpdated(): bool
    {
        list($lastCommitHash, $secondLastCommitHash) = $this->getTwoLatestCommits();

        exec('git diff --name-only ' . $lastCommitHash . ':composer.lock ' . $secondLastCommitHash . ':composer.lock', $output);

        return !empty($output);
    }

    /**
     * Remove meta data cache for DB changes
     */
    private function removeCache()
    {
        exec('cd ' . $this->getRootDir() . '/cache/metadata/ && rm -rf *', $output);
        exec('cd ' . $this->getRootDir() . '/cache/twig/ && rm -rf *', $output);
    }
}