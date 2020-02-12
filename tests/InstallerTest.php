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

namespace Zippovich2\ComposerInstaller\Test;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PreFileDownloadEvent;
use Composer\Util\RemoteFilesystem;
use PHPUnit\Framework\TestCase;
use Zippovich2\ComposerInstaller\Installer;

/**
 * @author Skoropadskyi Roman <zipo.ckorop@gmail.com>
 */
class InstallerTest extends TestCase
{
    public function tearDown(): void
    {
        // Removing environment variable
        \putenv('TEST_KEY');
    }

    public function testOnPreFileDownload(): void
    {
        \putenv('TEST_KEY=test');

        $event = $this->getMockBuilder(PreFileDownloadEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProcessedUrl', 'getRemoteFilesystem', 'setRemoteFilesystem'])
            ->getMock()
        ;

        $composer = $this->getMockBuilder(Composer::class)->disableOriginalConstructor()->getMock();

        $io = $this->getMockBuilder(IOInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();

        $rfs = $this->getMockBuilder(RemoteFilesystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOptions', 'isTlsDisabled'])
            ->getMockForAbstractClass()
        ;

        $event->expects(static::exactly(1))->method('getProcessedUrl')->willReturn('https://example/?key={%TEST_KEY%}');
        $event->expects(static::exactly(1))->method('getRemoteFilesystem')->willReturn($rfs);
        $event->expects(static::exactly(1))->method('setRemoteFilesystem');

        $rfs->expects(static::exactly(1))->method('getOptions')->willReturn([]);
        $rfs->expects(static::exactly(1))->method('isTlsDisabled')->willReturn(false);

        $installer = new Installer();
        $installer->activate($composer, $io);
        $installer->onPreFileDownload($event);
    }

    public function testException(): void
    {
        $this->expectException(\Exception::class);

        $event = $this->getMockBuilder(PreFileDownloadEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProcessedUrl', 'getRemoteFilesystem', 'setRemoteFilesystem'])
            ->getMock()
        ;

        $composer = $this->getMockBuilder(Composer::class)->disableOriginalConstructor()->getMock();

        $io = $this->getMockBuilder(IOInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();

        $event->expects(static::exactly(1))->method('getProcessedUrl')->willReturn('https://example/?key={%TEST_KEY%}');
        $event->expects(static::never())->method('getRemoteFilesystem');
        $event->expects(static::never())->method('setRemoteFilesystem');

        $installer = new Installer();
        $installer->activate($composer, $io);
        $installer->onPreFileDownload($event);
    }
}
