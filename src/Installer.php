<?php

declare(strict_types=1);

/*
 * This file is part of the "Composer Installer" composer plugin.
 *
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\ComposerInstaller;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;
use Symfony\Component\Dotenv\Dotenv;

/**
 * @author Skoropadskyi Roman <zipo.ckorop@gmail.com>
 */
class Installer implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Dotenv
     */
    private $dotenv;

    /**
     * @var string
     */
    private static $dir;

    public function __construct()
    {
        $this->dotenv = new Dotenv();
        self::$dir = \getcwd();
    }

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::PRE_FILE_DOWNLOAD => 'onPreFileDownload',
        ];
    }

    /**
     * Triggered before packages file start loading and change it urls replacing variables.
     *
     * @throws \Exception when url contain variable which is not in .env* files or not in environment variables
     */
    public function onPreFileDownload(PreFileDownloadEvent $event): void
    {
        $url = $event->getProcessedUrl();
        $placeholders = $this->getPlaceholders($url);

        if (\count($placeholders) > 0) {
            $envVars = $this->parseEnvVars();

            foreach ($placeholders as $placeholder) {
                $value = $envVars[$placeholder] ?? \getenv($placeholder);

                if (empty($value)) {
                    throw new \Exception(\sprintf('Missing environment variable "%s".', $placeholder));
                }
                $url = \str_replace('{%' . $placeholder . '%}', $value, $url);
            }

            $remoteFilesystem = $event->getRemoteFilesystem();

            $event->setRemoteFilesystem(
                new RemoteFilesystem(
                    $url,
                    $this->getIO(),
                    $this->getComposer()->getConfig(),
                    $remoteFilesystem->getOptions(),
                    $remoteFilesystem->isTlsDisabled()
                )
            );
        }
    }

    /**
     * Parse variables from package url.
     */
    private function getPlaceholders(?string $url): array
    {
        if (empty($url)) {
            return [];
        }

        \preg_match_all('/{%([A-Z0-9_]+)%}/', $url, $matches);

        return \array_unique($matches[1]);
    }

    /**
     * Parsing all .env* files and merge theme in order: .env, .env.local, .env.%APP_ENV%, .env.%APP_ENV%.local.
     */
    private function parseEnvVars(): array
    {
        $envVars = $this->parseEnvFile('.env');
        $envVars = $this->parseEnvFile('.env.local', $envVars);

        if (isset($envVars['APP_ENV'])) {
            $envVars = $this->parseEnvFile(\sprintf('.env.%s', $envVars['APP_ENV']), $envVars);
            $envVars = $this->parseEnvFile(\sprintf('.env.%s.local', $envVars['APP_ENV']), $envVars);
        }

        return $envVars;
    }

    /**
     * Parse .evn file and return it's values as array.
     *
     * @param string $file        path to the .env* file
     * @param array  $existedVars values to be merged
     *
     * @return array return parsed values which merged with provided $data values or $existedVars if file not exists
     */
    private function parseEnvFile(string $file, array $existedVars = []): array
    {
        $path = self::$dir . '/' . $file;

        if (!\file_exists($path)) {
            return $existedVars;
        }

        $data = \file_get_contents($path);

        return \array_merge($existedVars, $this->dotenv->parse($data));
    }

    public function getComposer(): Composer
    {
        return $this->composer;
    }

    public function getIO(): IOInterface
    {
        return $this->io;
    }
}
