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

use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem as ComposerRemoteFilesystem;

/**
 * @author Skoropadskyi Roman <zipo.ckorop@gmail.com>
 */
final class RemoteFileystem extends ComposerRemoteFilesystem
{
    /**
     * @var string
     */
    protected $fileUrl;

    public function __construct(
        string $fileUrl,
        IOInterface $io,
        Config $config = null,
        array $options = [],
        $disableTls = false
    ) {
        $this->fileUrl = $fileUrl;
        parent::__construct($io, $config, $options, $disableTls);
    }

    /**
     * {@inheritdoc}
     */
    public function copy(
        $originUrl,
        $fileUrl,
        $fileName,
        $progress = true,
        $options = []
    ) {
        return parent::copy(
            $originUrl,
            $this->fileUrl,
            $fileName,
            $progress,
            $options
        );
    }
}
