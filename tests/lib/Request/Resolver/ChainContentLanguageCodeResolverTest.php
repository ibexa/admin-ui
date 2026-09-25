<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Request\Resolver;

use Ibexa\AdminUi\Request\Resolver\ChainContentLanguageCodeResolver;
use Ibexa\Contracts\AdminUi\Request\Resolver\ContentLanguageCodeResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ChainContentLanguageCodeResolverTest extends TestCase
{
    public function testReturnsFirstNonEmptyLanguageCode(): void
    {
        $request = new Request();
        $resolver = new ChainContentLanguageCodeResolver([
            $this->createResolverMock(null),
            $this->createResolverMock(''),
            $this->createResolverMock('eng-GB'),
            $this->createResolverMock('ger-DE'),
        ]);

        self::assertSame('eng-GB', $resolver->resolve($request));
    }

    public function testReturnsNullWhenNoResolverMatches(): void
    {
        $resolver = new ChainContentLanguageCodeResolver([
            $this->createResolverMock(null),
            $this->createResolverMock(''),
        ]);

        self::assertNull($resolver->resolve(new Request()));
    }

    private function createResolverMock(?string $value): ContentLanguageCodeResolverInterface
    {
        $resolver = $this->createMock(ContentLanguageCodeResolverInterface::class);
        $resolver
            ->method('resolve')
            ->willReturn($value);

        return $resolver;
    }
}
