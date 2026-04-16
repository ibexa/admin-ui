<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Asset;

use Ibexa\Bundle\AdminUi\Asset\RtlEntrypointLookup;
use Ibexa\Contracts\AdminUi\Rtl\RtlModeResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

final class RtlEntrypointLookupTest extends TestCase
{
    private EntrypointLookup&MockObject $inner;

    private RtlModeResolverInterface&MockObject $rtlModeResolver;

    private RtlEntrypointLookup $lookup;

    protected function setUp(): void
    {
        $this->inner = $this->createMock(EntrypointLookup::class);
        $this->rtlModeResolver = $this->createMock(RtlModeResolverInterface::class);
        $this->lookup = new RtlEntrypointLookup($this->inner, $this->rtlModeResolver);
    }

    public function testGetJavaScriptFilesAlwaysDelegatesWithoutSwap(): void
    {
        $this->rtlModeResolver
            ->expects(self::never())
            ->method('isRtl');

        $this->inner
            ->expects(self::once())
            ->method('getJavaScriptFiles')
            ->with('foo')
            ->willReturn(['foo.js']);

        self::assertSame(['foo.js'], $this->lookup->getJavaScriptFiles('foo'));
    }

    public function testGetCssFilesSwapsToRtlVariantWhenRtlAndVariantExists(): void
    {
        $this->rtlModeResolver
            ->expects(self::once())
            ->method('isRtl')
            ->willReturn(true);

        $this->inner
            ->expects(self::exactly(2))
            ->method('entryExists')
            ->willReturnMap([
                ['foo-rtl', true],
                ['foo-rtl-override', false],
            ]);

        $this->inner
            ->expects(self::once())
            ->method('getCssFiles')
            ->with('foo-rtl')
            ->willReturn(['foo-rtl.css']);

        self::assertSame(['foo-rtl.css'], $this->lookup->getCssFiles('foo'));
    }

    public function testGetCssFilesAppendsOverrideWhenRtlVariantAndOverrideExist(): void
    {
        $this->rtlModeResolver
            ->expects(self::once())
            ->method('isRtl')
            ->willReturn(true);

        $this->inner
            ->expects(self::exactly(2))
            ->method('entryExists')
            ->willReturnMap([
                ['foo-rtl', true],
                ['foo-rtl-override', true],
            ]);

        $this->inner
            ->expects(self::exactly(2))
            ->method('getCssFiles')
            ->willReturnMap([
                ['foo-rtl', ['foo-rtl.css']],
                ['foo-rtl-override', ['foo-rtl-override.css']],
            ]);

        self::assertSame(
            ['foo-rtl.css', 'foo-rtl-override.css'],
            $this->lookup->getCssFiles('foo'),
        );
    }

    public function testGetCssFilesDoesNotSwapWhenRtlVariantDoesNotExist(): void
    {
        $this->rtlModeResolver
            ->expects(self::once())
            ->method('isRtl')
            ->willReturn(true);

        $this->inner
            ->expects(self::exactly(2))
            ->method('entryExists')
            ->willReturnMap([
                ['foo-rtl', false],
                ['foo-override', false],
            ]);

        $this->inner
            ->expects(self::once())
            ->method('getCssFiles')
            ->with('foo')
            ->willReturn(['foo.css']);

        self::assertSame(['foo.css'], $this->lookup->getCssFiles('foo'));
    }

    public function testGetCssFilesAppendsOverrideWhenOnlyOverrideExists(): void
    {
        $this->rtlModeResolver
            ->expects(self::once())
            ->method('isRtl')
            ->willReturn(true);

        $this->inner
            ->expects(self::exactly(2))
            ->method('entryExists')
            ->willReturnMap([
                ['foo-rtl', false],
                ['foo-override', true],
            ]);

        $this->inner
            ->expects(self::exactly(2))
            ->method('getCssFiles')
            ->willReturnMap([
                ['foo', ['foo.css']],
                ['foo-override', ['foo-override.css']],
            ]);

        self::assertSame(
            ['foo.css', 'foo-override.css'],
            $this->lookup->getCssFiles('foo'),
        );
    }

    public function testGetCssFilesDoesNotSwapWhenNotRtl(): void
    {
        $this->rtlModeResolver
            ->expects(self::once())
            ->method('isRtl')
            ->willReturn(false);

        $this->inner
            ->expects(self::never())
            ->method('entryExists');

        $this->inner
            ->expects(self::once())
            ->method('getCssFiles')
            ->with('foo')
            ->willReturn(['foo.css']);

        self::assertSame(['foo.css'], $this->lookup->getCssFiles('foo'));
    }

    public function testGetCssFilesDoesNotSwapWhenInnerIsNotEntrypointLookup(): void
    {
        $inner = $this->createMock(EntrypointLookupInterface::class);
        $lookup = new RtlEntrypointLookup($inner, $this->rtlModeResolver);

        $this->rtlModeResolver
            ->expects(self::once())
            ->method('isRtl')
            ->willReturn(true);

        $inner
            ->expects(self::once())
            ->method('getCssFiles')
            ->with('foo')
            ->willReturn(['foo.css']);

        self::assertSame(['foo.css'], $lookup->getCssFiles('foo'));
    }

    public function testGetIntegrityDataDelegatesWhenInnerSupportsIt(): void
    {
        $this->inner
            ->expects(self::once())
            ->method('getIntegrityData')
            ->willReturn(['foo.css' => 'sha384-abc']);

        self::assertSame(['foo.css' => 'sha384-abc'], $this->lookup->getIntegrityData());
    }

    public function testResetDelegatesToInner(): void
    {
        $this->inner->expects(self::once())->method('reset');

        $this->lookup->reset();
    }
}
