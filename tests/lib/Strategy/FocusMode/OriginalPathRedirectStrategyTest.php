<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Strategy\FocusMode;

use Ibexa\AdminUi\Strategy\FocusMode\OriginalPathRedirectStrategy;
use PHPUnit\Framework\TestCase;

final class OriginalPathRedirectStrategyTest extends TestCase
{
    private OriginalPathRedirectStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new OriginalPathRedirectStrategy();
    }

    /**
     * @dataProvider dataProviderForTestSupports
     */
    public function testSupports(string $path, bool $doesSupport): void
    {
        self::assertSame($doesSupport, $this->strategy->supports($path));
    }

    /**
     * @return iterable<array{string, bool}>
     */
    public function dataProviderForTestSupports(): iterable
    {
        yield ['foo.path', false];

        yield ['ibexa.section.list', false];

        yield ['ibexa.content.view', true];
    }

    /**
     * @dataProvider dataProviderForTestGenerateRedirectPath
     */
    public function testGenerateRedirectPath(string $path, string $expectedPath): void
    {
        self::assertSame(
            $expectedPath,
            $this->strategy->generateRedirectPath($path)
        );
    }

    /**
     * @return iterable<array{string, string}>
     */
    public function dataProviderForTestGenerateRedirectPath(): iterable
    {
        yield ['foo.path', 'foo.path'];

        yield ['ibexa.calendar.view', 'ibexa.calendar.view'];
    }
}
