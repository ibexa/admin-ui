<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Siteaccess;

use Ibexa\AdminUi\Siteaccess\SiteAccessInfoResolver;
use Ibexa\Contracts\AdminUi\Siteaccess\SiteAccessInfo;
use Ibexa\Contracts\AdminUi\Siteaccess\SiteAccessInfoProviderInterface;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SiteAccessInfoResolver::class)]
final class SiteAccessInfoResolverTest extends TestCase
{
    public function testResolveUsesTheFirstSupportingProvider(): void
    {
        $resolver = new SiteAccessInfoResolver([
            $this->createProvider('corporate', new SiteAccessInfo('Corporate', 'corporate.example.com')),
            $this->createProvider('acme', new SiteAccessInfo('Acme (first)', 'acme.example.com')),
            $this->createProvider('acme', new SiteAccessInfo('Acme (second)')),
        ]);

        $info = $resolver->resolve(new SiteAccess('acme'));

        self::assertSame('Acme (first)', $info->getName());
        self::assertSame('acme.example.com', $info->getDomain());
    }

    public function testResolveFallsBackToTheSiteAccessName(): void
    {
        $resolver = new SiteAccessInfoResolver([
            $this->createProvider('corporate', new SiteAccessInfo('Corporate')),
        ]);

        $info = $resolver->resolve(new SiteAccess('site'));

        self::assertSame('site', $info->getName());
        self::assertNull($info->getDomain());
    }

    public function testResolveWithoutProviders(): void
    {
        $info = (new SiteAccessInfoResolver([]))->resolve(new SiteAccess('site'));

        self::assertSame('site', $info->getName());
        self::assertNull($info->getDomain());
    }

    private function createProvider(
        string $supportedSiteAccessName,
        SiteAccessInfo $info
    ): SiteAccessInfoProviderInterface {
        $provider = self::createStub(SiteAccessInfoProviderInterface::class);
        $provider
            ->method('supports')
            ->willReturnCallback(
                static fn (SiteAccess $siteAccess): bool => $siteAccess->name === $supportedSiteAccessName
            );
        $provider->method('resolve')->willReturn($info);

        return $provider;
    }
}
