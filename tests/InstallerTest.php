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
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
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

        /**
         * @var PreFileDownloadEvent|MockObject $event
         */
        $event = $this->getMockBuilder(PreFileDownloadEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProcessedUrl', 'setProcessedUrl'])
            ->getMock()
        ;

        /**
         * @var Composer|MockBuilder $composer
         */
        $composer = $this->getMockBuilder(Composer::class)->disableOriginalConstructor()->getMock();

        /**
         * @var IOInterface|MockBuilder $io
         */
        $io = $this->getMockBuilder(IOInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();

        $event->expects(static::exactly(1))->method('getProcessedUrl')->willReturn('https://example/?key={%TEST_KEY%}');
        $event->expects(static::exactly(1))->method('setProcessedUrl');

        $installer = new Installer();
        $installer->activate($composer, $io);
        $installer->onPreFileDownload($event);
    }

    public function testException(): void
    {
        $this->expectException(\Exception::class);

        /**
         * @var PreFileDownloadEvent|MockObject $event
         */
        $event = $this->getMockBuilder(PreFileDownloadEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProcessedUrl', 'setProcessedUrl'])
            ->getMock()
        ;

        /**
         * @var Composer|MockBuilder $composer
         */
        $composer = $this->getMockBuilder(Composer::class)->disableOriginalConstructor()->getMock();

        /**
         * @var IOInterface|MockBuilder $io
         */
        $io = $this->getMockBuilder(IOInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();

        $event->expects(static::exactly(1))->method('getProcessedUrl')->willReturn('https://example/?key={%TEST_KEY%}');
        $event->expects(static::never())->method('setProcessedUrl');

        $installer = new Installer();
        $installer->activate($composer, $io);
        $installer->onPreFileDownload($event);
    }
}
